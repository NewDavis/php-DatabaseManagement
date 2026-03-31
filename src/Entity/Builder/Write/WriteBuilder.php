<?php

namespace NewDavis\DatabaseManagement\Entity\Builder\Write;

use NewDavis\DatabaseManagement\Entity\AbstractEntity;
use NewDavis\DatabaseManagement\Entity\AbstractEntityCollection;
use NewDavis\DatabaseManagement\Entity\Builder\Table\TableBuilder;
use NewDavis\DatabaseManagement\Entity\Builder\Table\TemporaryTableBuilder;
use NewDavis\DatabaseManagement\Entity\EntityDefinitionInterface;
use NewDavis\DatabaseManagement\Entity\EntityRegistry;
use NewDavis\DatabaseManagement\Entity\Exception\Table\FieldNotFoundException;
use NewDavis\DatabaseManagement\Entity\Field\FieldCollection;
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
        $this->temporaryWriteBuilder = new TemporaryWriteBuilder($this->definition, $this);
    }

    public function buildProperties(FieldCollection $fields): string
    {
        $properties = array_map(
            fn(ScalarField $scalarField) => "`{$scalarField->getStorageName()}`",
            $fields->filter(
                ScalarField::class
            )
        );

        return implode(', ', $properties);
    }

    public function buildPlaceholderFromValues(
        FieldCollection $fields,
        array $values,
        AbstractEntityCollection|array $collection
    ): string {
        $rows = [];

        for ($i = 0; $i < count($collection); $i++) {
            $placeholder = array_map(
                function (ScalarField $scalarField) use ($values, $i) {
                    $key = $this->buildValueKey($i, $scalarField);

                    if (!array_key_exists($key, $values)) {
                        return ORM::DEFAULT->value;
                    }

                    return ":" . $key;
                },
                $fields->filter(
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

    public function buildValueKey(int $row, ScalarField $scalarField): string
    {
        return "r{$row}_{$this->definition->getEntityName()}_{$scalarField->getStorageName()}";
    }

    public function buildMappingStatements(AbstractEntityCollection $collection, WriteAction $action): EntityWriteStatementCollection
    {
        $statements = new EntityWriteStatementCollection([]);

        /** @var ManyToManyRelation $relation */
        foreach ($this->definition->getFields()->filter(ManyToManyRelation::class) as $relation) {
            foreach ($this->mappingWriteBuilder->build($relation, $collection, $action) as $statement) {
                $statements->add($statement);
            }
        }

        return $statements;
    }

    public function build(
        WriteAction $action,
        AbstractEntityCollection $collection,
        array $rows = []
    ): EntityWriteStatementCollection {
        $queries = new EntityWriteStatementCollection([]);

        if (
            $action == WriteAction::UPDATE &&
            count($groups = EntityHelper::groupByColumns($rows)) == 0
        ) {
            return $queries;
        }

        $this->cache = new EntityWriteCache($action, $this->definition, $this->registry, $collection);

        $properties = $this->buildProperties($this->definition->getFields());
        $values = $this->buildValues($collection);
        $placeholder = $this->buildPlaceholderFromValues(
            $this->definition->getFields(),
            $values,
            $collection
        );

        // persist related entities first
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

        // main query building
        if ($action == WriteAction::CREATE) {
            $queries->add(new EntityWriteStatement(<<<SQL
INSERT INTO `{$this->definition->getEntityName()}`
({$properties})
VALUES
{$placeholder}
SQL, $values));
        } else if ($action == WriteAction::UPDATE) {
            $queries->add($this->temporaryTableBuilder->create());

            foreach ($groups as $columns => $group) {
                $columnsArray = new FieldCollection(
                    array_filter(array_map(
                        fn($column) => $this->definition->getFields()->getByInternalName($column),
                        explode('|', $columns)
                    )),
                    'tmp_' . $this->definition->getEntityName()
                );

                $queries->add($this->temporaryTableBuilder->truncate());
                $queries->add($this->temporaryWriteBuilder->insertInTemporary($columnsArray, $group));
                $queries->add($this->temporaryWriteBuilder->updateOriginal($columnsArray));
            }
        }

        $mappingStatements = $this->buildMappingStatements($collection, $action);
        foreach ($mappingStatements as $statement) {
            $queries->add($statement);
        }

        return $queries;
    }
}
