<?php

namespace NewDavis\DatabaseManagement\Entity\Builder\Write;

use NewDavis\DatabaseManagement\Entity\EntityDefinitionInterface;
use NewDavis\DatabaseManagement\Entity\Field\FieldCollection;
use NewDavis\DatabaseManagement\Entity\Field\Scalar\IdField;
use NewDavis\DatabaseManagement\Entity\Field\Scalar\ScalarField;
use NewDavis\DatabaseManagement\Entity\Write\EntityWriteStatement;
use NewDavis\DatabaseManagement\ORM;

class TemporaryWriteBuilder
{
    public function __construct(
        private readonly EntityDefinitionInterface $definition,
        private readonly WriteBuilder $writeBuilder
    ) {
    }

    private function buildValues(FieldCollection $fields, array $group): array
    {
        $values = [];

        for ($i = 0; $i < count($group); $i++) {
            $entity = $group[$i];

            /** @var ScalarField $scalarField */
            foreach (
                $fields->filter(
                    ScalarField::class
                )
                as $scalarField
            ) {
                $value = $scalarField->getSerializer()->encode(
                    $entity[$scalarField->getInternalName()]
                );

                if ($value === ORM::DEFAULT) continue;

                $values[$this->writeBuilder->buildValueKey($i, $scalarField)] = $value;
            }
        }

        return $values;
    }

    public function insertInTemporary(FieldCollection $fields, array $group): EntityWriteStatement
    {
        $properties = $this->writeBuilder->buildProperties($this->definition->getFields());
        $values = $this->buildValues($fields, $group);
        $placeholder = $this->writeBuilder->buildPlaceholderFromValues(
            $this->definition->getFields(),
            $values,
            $group
        );

        return new EntityWriteStatement(<<<SQL
INSERT INTO `{$fields->getEntityName()}`
({$properties})
VALUES
{$placeholder}
SQL, $values);
    }

    public function updateOriginal(FieldCollection $fields): EntityWriteStatement
    {
        $setQuery = implode(',',
            array_filter(
                array_map(
                    function (ScalarField $field) use ($fields) {
                        if ($field instanceof IdField) return null;

                        return <<<SQL
`{$this->definition->getEntityName()}`.`{$field->getStorageName()}` = `{$fields->getEntityName()}`.`{$field->getStorageName()}`
SQL;
                    },
                    $fields->filter(ScalarField::class)
                )
            )
        );

        return new EntityWriteStatement(<<<SQL
UPDATE `{$this->definition->getEntityName()}`
JOIN `{$fields->getEntityName()}`
    ON `{$this->definition->getEntityName()}`.`id` = `{$fields->getEntityName()}`.`id`
SET {$setQuery}
SQL, []);
    }
}