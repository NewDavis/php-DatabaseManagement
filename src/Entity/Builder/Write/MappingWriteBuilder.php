<?php

namespace NewDavis\DatabaseManagement\Entity\Builder\Write;

use Doctrine\ORM\Mapping\Embeddable;
use NewDavis\DatabaseManagement\Entity\AbstractEntity;
use NewDavis\DatabaseManagement\Entity\AbstractEntityCollection;
use NewDavis\DatabaseManagement\Entity\EntityDefinitionInterface;
use NewDavis\DatabaseManagement\Entity\EntityRegistry;
use NewDavis\DatabaseManagement\Entity\Field\FieldCollection;
use NewDavis\DatabaseManagement\Entity\Field\Relational\FkField;
use NewDavis\DatabaseManagement\Entity\Field\Relational\ManyToManyRelation;
use NewDavis\DatabaseManagement\Entity\Field\Serializable;
use NewDavis\DatabaseManagement\Entity\Write\EntityWriteStatement;
use NewDavis\DatabaseManagement\Entity\Write\EntityWriteStatementCollection;
use NewDavis\DatabaseManagement\Util\Helper\EntityTableHelper;

class MappingWriteBuilder
{
    public function __construct(
        private readonly EntityRegistry $registry,
        private readonly EntityDefinitionInterface $definition
    ) {
    }

    public function buildProperties(FieldCollection $mappingFields): string
    {
        $properties = array_map(
            fn(FkField $fkField) => "`{$fkField->getStorageName()}`",
            $mappingFields->filter(FkField::class)
        );

        return implode(', ', $properties);
    }

    public function buildValues(
        ManyToManyRelation $manyToManyRelation,
        FieldCollection $mappingFields,
        AbstractEntityCollection $collection
    ): array {
        $values = [];

        foreach ($collection as $topLevelEntity) {
            $mappedData = $this->fetchManyToManyCollectionFromEntity($manyToManyRelation, $topLevelEntity);

            for ($j = 0; $j < $mappedData->count(); $j++) {
                /** @var AbstractEntity $mappedEntityData */
                $mappedEntityData = $mappedData->indexAt($j);

                /** @var FkField $fkField */
                foreach (
                    $mappingFields->filter(FkField::class)
                    as $fkField
                ) {
                    if ($fkField->getRelatedToDefinition() == $this->definition::class) {
                        $propertyField = $this->definition->getFields()->getByInternalName(
                            $manyToManyRelation->getRelatedByInternalName()
                        );
                        if (!$propertyField instanceof Serializable) continue;

                        $value = $topLevelEntity->get(
                            $propertyField,
                            $manyToManyRelation->getRelatedByInternalName()
                        );
                    } else if ($fkField->getRelatedToDefinition() == $mappedEntityData::getDefinitionClass()) {
                        $relatedDefinition = $this->registry->getDefinitionByDefinitionClass($fkField->getRelatedToDefinition());
                        if ($relatedDefinition == null) continue;

                        $relatedPropertyField = $relatedDefinition->getFields()->getByInternalName(
                            $manyToManyRelation->getRelatedToInternalName()
                        );
                        if (!$relatedPropertyField instanceof Serializable) continue;

                        $value = $mappedEntityData->get(
                            $relatedPropertyField,
                            $manyToManyRelation->getRelatedToInternalName()
                        );
                    } else {
                        dd("MappingWriteBuilder#buildValues: No value found for mapping");
                    }

                    $values[$this->buildValueKey($j, $mappingFields->getEntityName(), $fkField)] = $value;
                }
            }
        }

        return $values;
    }

    public function buildPlaceholderFromValues(
        array $values
    ): string {
        $rows = [];

        $keys = array_keys($values);
        for ($i = 0; $i < count($values); $i += 2) {
            $rows[$i / 2] = '(:' . $keys[$i] . ', :' . $keys[$i+1] . ')';
        }

        return implode(",\n", $rows);
    }

    private function buildValueKey(int $row, string $mappingTableName, FkField $fkField): string
    {
        return "r{$row}_{$mappingTableName}_{$fkField->getStorageName()}";
    }

    private function fetchManyToManyCollectionFromEntity(
        ManyToManyRelation $manyToManyRelation,
        AbstractEntity $entity
    ): AbstractEntityCollection {
        return $entity->get(
            $manyToManyRelation,
            $manyToManyRelation->getInternalName()
        );
    }

    private function buildDelete(
        AbstractEntityCollection $collection,
        string $mappingTableName,
        FieldCollection $mappingFields
    ): EntityWriteStatement|null {
        $field = null;
        /** @var FkField $fkField */
        foreach ($mappingFields as $fkField) {
            if ($fkField->getRelatedToDefinition() != $this->definition::class) {
                continue;
            }

            $field = $fkField;
        }

        if ($field == null) return null;

        $placeholders = implode(', ', array_map(
            fn(AbstractEntity $entity) => '?',
            $collection->getEntities()
        ));

        try {
            /** @var Serializable $relatedField */
            $relatedField = $this->definition->getFields()->getByInternalName($field->getRelatedToInternalName());
        } catch (\Exception $e) {
            return null;
        }

        $affectedIds = array_values(array_map(
            fn(AbstractEntity $entity) => $entity->get(
                $relatedField,
                $relatedField->getInternalName()
            ),
            $collection->getEntities()
        ));

        return new EntityWriteStatement(
            <<<SQL
DELETE FROM `{$mappingTableName}`
WHERE `{$field->getStorageName()}` IN ({$placeholders})
SQL,
            $affectedIds
        );
    }

    public function build(
        ManyToManyRelation $manyToManyRelation,
        AbstractEntityCollection $collection,
        WriteAction $action
    ): EntityWriteStatementCollection {
        $mappingTableName = EntityTableHelper::buildMappingTableName(
            $this->definition,
            $this->registry,
            $manyToManyRelation
        );

        $mappingFields = EntityTableHelper::buildMappingTableFields(
            $this->definition,
            $this->registry,
            $manyToManyRelation,
            $mappingTableName
        );

        $properties = $this->buildProperties($mappingFields);
        $values = $this->buildValues($manyToManyRelation, $mappingFields, $collection);
        if (empty($values)) {
            return new EntityWriteStatementCollection([]);
        }

        $placeholder = $this->buildPlaceholderFromValues($values);

        $query = <<<SQL
INSERT INTO `{$mappingTableName}`
({$properties})
VALUES
{$placeholder}
SQL;

        $queries = new EntityWriteStatementCollection([]);

        if ($action == WriteAction::UPDATE) {
            $queries->add($this->buildDelete($collection, $mappingTableName, $mappingFields));
        }

        $queries->add(new EntityWriteStatement($query, $values));

        return $queries;
    }
}