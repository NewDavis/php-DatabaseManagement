<?php

namespace NewDavis\DatabaseManagement\Entity\Builder\Read\Mapping;

use NewDavis\DatabaseManagement\Entity\Builder\Condition\ConditionBuilder;
use NewDavis\DatabaseManagement\Entity\EntityIdCollection;
use NewDavis\DatabaseManagement\Entity\Field\Relational\ManyToManyRelation;
use NewDavis\DatabaseManagement\Entity\Field\Relational\OneToManyRelation;
use NewDavis\DatabaseManagement\Entity\Read\Criteria\Criteria;
use NewDavis\DatabaseManagement\Entity\Read\EntityReadStatement;
use NewDavis\DatabaseManagement\Entity\Read\EntityReadStatementCollection;
use NewDavis\DatabaseManagement\Util\Helper\EntityTableHelper;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class ReadMappingIdBuilder extends ConditionBuilder
{
    public function build(
        ManyToManyRelation|OneToManyRelation $relation,
        EntityIdCollection $idCollection
    ): EntityReadStatementCollection {
        $targetTable = $this->getTargetTable($relation);
        $targetFields = $this->getTargetFields($relation, $targetTable);
        $targetConditionField = $this->getTargetConditionField($relation, $targetTable);

        $queryIdList = implode(
            ', ',
            array_map(
                fn(UuidInterface $uuid) => '?',
                $idCollection->getIds()
            )
        );

        $query = <<<SQL
SELECT {$targetFields} FROM `{$targetTable}` 
WHERE `{$targetConditionField}` IN ({$queryIdList}) 
SQL;

        return new EntityReadStatementCollection([
            new EntityReadStatement(
                $query,
                array_map(
                    fn(UuidInterface $uuid) => $uuid->getBytes(),
                    $idCollection->getIds()
                )
            )
        ]);
    }

    private function getTargetTable(ManyToManyRelation|OneToManyRelation $relation): string
    {
        if ($relation instanceof ManyToManyRelation) {
            return EntityTableHelper::buildMappingTableName($this->definition, $this->registry, $relation);
        }

        return $this->registry->getDefinitionByDefinitionClass($relation->getRelatedToDefinition())->getEntityName();
    }

    private function getTargetFields(ManyToManyRelation|OneToManyRelation $relation, string $tableName): string
    {
        $targetFields = [];

        if ($relation instanceof ManyToManyRelation) {
            $fields = EntityTableHelper::buildMappingTableFields(
                $this->definition,
                $this->registry,
                $relation,
                $tableName
            );

            if ($fields->getFields()[0]->getRelatedToDefinition() === $relation->getRelatedToDefinition()) {
                $targetFields[0] = $fields->getFields()[1]->getStorageName();
                $targetFields[1] = $fields->getFields()[0]->getStorageName();
            } else {
                $targetFields[0] = $fields->getFields()[0]->getStorageName();
                $targetFields[1] = $fields->getFields()[1]->getStorageName();
            }
        } else {
            $relatedDefinition = $this->registry->getDefinitionByDefinitionClass($relation->getRelatedToDefinition());
            $relatedField = $relatedDefinition->getFields()->getByInternalName($relation->getRelatedToInternalName());

            $targetFields[0] = $relatedField->getStorageName();
            $targetFields[1] = 'id';
        }

        $targetFields = array_map(
            fn(string $property) => '`' . $property . '`',
            $targetFields
        );

        $targetFields[0] .= ' AS `key`';
        $targetFields[1] .= ' AS `value`';

        return implode(', ', $targetFields);
    }

    private function getTargetConditionField(ManyToManyRelation|OneToManyRelation $relation, string $tableName): string
    {
        if ($relation instanceof ManyToManyRelation) {
            $fields = EntityTableHelper::buildMappingTableFields(
                $this->definition,
                $this->registry,
                $relation,
                $tableName
            );

            if ($fields->getFields()[0]->getRelatedToDefinition() === $relation->getRelatedToDefinition()) {
                return $fields->getFields()[1]->getStorageName();
            }

            return $fields->getFields()[0]->getStorageName();
        }

        $relatedDefinition = $this->registry->getDefinitionByDefinitionClass($relation->getRelatedToDefinition());
        $relatedField = $relatedDefinition->getFields()->getByInternalName($relation->getRelatedToInternalName());

        return $relatedField->getStorageName();
    }
}