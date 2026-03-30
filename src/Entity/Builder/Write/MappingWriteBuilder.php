<?php

namespace NewDavis\DatabaseManagement\Entity\Builder\Write;

use NewDavis\DatabaseManagement\Entity\AbstractEntity;
use NewDavis\DatabaseManagement\Entity\AbstractEntityCollection;
use NewDavis\DatabaseManagement\Entity\EntityDefinitionInterface;
use NewDavis\DatabaseManagement\Entity\EntityRegistry;
use NewDavis\DatabaseManagement\Entity\Field\FieldCollection;
use NewDavis\DatabaseManagement\Entity\Field\Relational\FkField;
use NewDavis\DatabaseManagement\Entity\Field\Relational\ManyToManyRelation;
use NewDavis\DatabaseManagement\Entity\Field\Serializable;
use NewDavis\DatabaseManagement\Entity\Write\EntityWriteStatement;
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

        for ($i = 0; $i < $collection->count(); $i++) {
            $entity = $collection->indexAt($i);

            $mappedData = $this->fetchManyToManyCollectionFromEntity($manyToManyRelation, $entity);

            /** @var AbstractEntity $mappedEntityData */
            foreach ($mappedData as $mappedEntityData) {
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

                        $value = $entity->get(
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

                    $values[$this->buildValueKey($i, $mappingFields->getEntityName(), $fkField)] = $value;
                }
            }
        }

        return $values;
    }

    public function buildPlaceholderFromValues(
        array $values,
        FieldCollection $mappingFields,
        AbstractEntityCollection $collection
    ): string {
        $rows = [];

        for ($i = 0; $i < $collection->count(); $i++) {
            $placeholder = array_map(
                function (FkField $fkField) use ($values, $mappingFields, $i) {
                    $key = $this->buildValueKey($i, $mappingFields->getEntityName(), $fkField);

                    return ":" . $key;
                },
                $mappingFields->filter(FkField::class)
            );

            $rows[$i] = '(' . implode(', ', $placeholder) . ')';
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

    public function build(
        ManyToManyRelation $manyToManyRelation,
        AbstractEntityCollection $collection
    ): EntityWriteStatement {
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
        $placeholder = $this->buildPlaceholderFromValues($values, $mappingFields, $collection);

        $query = <<<SQL
INSERT INTO `{$mappingTableName}`
({$properties})
VALUES
{$placeholder}
SQL;

        return new EntityWriteStatement($query, $values);
    }
}