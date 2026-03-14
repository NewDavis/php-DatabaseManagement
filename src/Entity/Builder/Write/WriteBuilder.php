<?php

namespace NewDavis\DatabaseManagement\Entity\Builder\Write;

use NewDavis\DatabaseManagement\Entity\AbstractEntityCollection;
use NewDavis\DatabaseManagement\Entity\EntityDefinitionInterface;
use NewDavis\DatabaseManagement\Entity\EntityRegistry;
use NewDavis\DatabaseManagement\Entity\Field\Scalar\ScalarField;
use NewDavis\DatabaseManagement\Entity\Write\EntityWriteStatement;
use NewDavis\DatabaseManagement\Entity\Write\EntityWriteStatementCollection;

class WriteBuilder
{
    public function __construct(
        private readonly EntityRegistry $registry,
        private readonly EntityDefinitionInterface $definition
    ) {
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

    public function buildPlaceholder(int $rowCount): string
    {
        $rows = [];

        for ($i = 0; $i < $rowCount; $i++) {
            $placeholder = array_map(
                fn(ScalarField $scalarField) => ":" . $this->buildValueKey($i, $scalarField),
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
            $entity = $collection->getIndex($i);

            foreach (
                $this->definition->getFields()->filter(
                    ScalarField::class
                )
                as $scalarField
            ) {
                $values[
                    $this->buildValueKey($i, $scalarField)
                ] = $entity->get($scalarField->getInternalName());
            }
        }

        return $values;
    }

    private function buildValueKey(int $row, ScalarField $scalarField): string
    {
        return "r{$row}-{$scalarField->getStorageName()}";
    }

    public function build(AbstractEntityCollection $collection): EntityWriteStatementCollection
    {
        $properties = $this->buildProperties();
        $placeholder = $this->buildPlaceholder($collection->count());

        $query = <<<SQL
INSERT INTO `{$this->definition->getEntityName()}`
({$properties})
VALUES
{$placeholder}
SQL;

        $values = $this->buildValues($collection);

        return new EntityWriteStatementCollection(
            [new EntityWriteStatement($query, $values)]
        );
    }
}