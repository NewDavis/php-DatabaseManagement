<?php

namespace NewDavis\DatabaseManagement\Entity\Builder\Write;

use NewDavis\DatabaseManagement\Entity\AbstractEntity;
use NewDavis\DatabaseManagement\Entity\AbstractEntityCollection;
use NewDavis\DatabaseManagement\Entity\Builder\Table\TableBuilder;
use NewDavis\DatabaseManagement\Entity\Builder\Table\TemporaryTableBuilder;
use NewDavis\DatabaseManagement\Entity\EntityDefinitionInterface;
use NewDavis\DatabaseManagement\Entity\EntityRegistry;
use NewDavis\DatabaseManagement\Entity\Field\Relational\ManyToManyRelation;
use NewDavis\DatabaseManagement\Entity\Field\Scalar\ScalarField;
use NewDavis\DatabaseManagement\Entity\Write\EntityWriteStatement;
use NewDavis\DatabaseManagement\Entity\Write\EntityWriteStatementCollection;
use NewDavis\DatabaseManagement\ORM;
use NewDavis\DatabaseManagement\Util\Helper\EntityHelper;

class WriteBuilder
{
    private readonly MappingWriteBuilder $mappingWriteBuilder;
    private readonly TemporaryTableBuilder $temporaryTableBuilder;
    private readonly TemporaryWriteBuilder $temporaryWriteBuilder;
    private EntityWriteCache $cache;

    public function __construct(
        private readonly EntityRegistry $registry,
        private readonly EntityDefinitionInterface $definition
    ) {
        $this->mappingWriteBuilder = new MappingWriteBuilder($this->registry, $this->definition);
        $this->temporaryTableBuilder = new TemporaryTableBuilder(
            TableBuilder::fromDefinition($this->registry, $this->definition)
        );
        $this->temporaryWriteBuilder = new TemporaryWriteBuilder($this->registry, $this->definition);
    }

    public function buildProperties(): string
    {
        $properties = array_map(
            fn(ScalarField $scalarField) => "`{$scalarField->getStorageName()}`",
            $this->definition->getFields()->filter(
                ScalarField::class
            )
        );

        return implode(', ', $properties);
    }

    public function buildPlaceholderFromValues(array $values, AbstractEntityCollection $collection): string
    {
        $rows = [];

        for ($i = 0; $i < $collection->count(); $i++) {
            $placeholder = array_map(
                function (ScalarField $scalarField) use ($values, $i) {
                    $key = $this->buildValueKey($i, $scalarField);

                    if (!array_key_exists($key, $values)) {
                        return ORM::DEFAULT->value;
                    }

                    return ":" . $key;
                },
                $this->definition->getFields()->filter(
                    ScalarField::class
                )
            );

            $rows[$i] = '(' . implode(', ', $placeholder) . ')';
        }

        return implode(",\n", $rows);
    }

    public function buildValues(AbstractEntityCollection $collection): array
    {
        $values = [];

        for ($i = 0; $i < $collection->count(); $i++) {
            $entity = $collection->indexAt($i);

            foreach (
                $this->definition->getFields()->filter(
                    ScalarField::class
                )
                as $scalarField
            ) {
                $value = $entity->get(
                    $scalarField,
                    $scalarField->getInternalName()
                );

                if ($value === ORM::DEFAULT) continue;

                $values[$this->buildValueKey($i, $scalarField)] = $value;
            }
        }

        return $values;
    }

    private function buildValueKey(int $row, ScalarField $scalarField): string
    {
        return "r{$row}_{$this->definition->getEntityName()}_{$scalarField->getStorageName()}";
    }

    public function buildMappingStatements(AbstractEntityCollection $collection): EntityWriteStatementCollection
    {
        return new EntityWriteStatementCollection(
            array_map(
                fn(ManyToManyRelation $relation) => $this->mappingWriteBuilder->build($relation, $collection),
                $this->definition->getFields()->filter(ManyToManyRelation::class)
            )
        );
    }

    public function build(
        WriteAction $action, AbstractEntityCollection $collection, array $rows = [], bool $temp = false
    ): EntityWriteStatementCollection {
        $queries = new EntityWriteStatementCollection([]);

        $this->cache = new EntityWriteCache($action, $this->definition, $this->registry, $collection);

        $properties = $this->buildProperties();
        $values = $this->buildValues($collection);
        $placeholder = $this->buildPlaceholderFromValues($values, $collection);

        foreach ($this->cache->collectEntities(false) as $definitionClass => $entities) {
            $repository = $this->registry->getRepositoryByDefinitionClass($definitionClass);
            $definition = $this->registry->getDefinitionByDefinitionClass($definitionClass);

            $notPersisted = array_filter(
                $entities,
                fn(AbstractEntity $entity) => !$this->cache->exists($definition, $entity->getId())
            );

            if (count($notPersisted) == 0) continue;

            $notPersistedCollection = EntityHelper::createCollection($definition, $notPersisted);

            foreach ($repository->getWriteBuilder()->build(
                WriteAction::CREATE, $notPersistedCollection
            )->getStatements() as $statement) {
                $queries->add($statement);
            }
        }

        if ($action == WriteAction::CREATE) {
            $queries->add(new EntityWriteStatement(<<<SQL
INSERT INTO `{$this->definition->getEntityName()}`
({$properties})
VALUES
{$placeholder}
SQL, $values));
        } else if ($action == WriteAction::UPDATE) {
            foreach ($this->temporaryTableBuilder->build($rows) as $statement) {
                $queries->add($statement);
            }

            foreach ($this->temporaryWriteBuilder->writeInTemporaryTable($rows) as $statement) {
                $queries->add($statement);
            }
        }

        $mappingStatements = $this->buildMappingStatements($collection);
        foreach ($mappingStatements as $statement) {
            $queries->add($statement);
        }

        return $queries;
    }
}
