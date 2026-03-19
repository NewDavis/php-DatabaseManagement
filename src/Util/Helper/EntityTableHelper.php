<?php

namespace NewDavis\DatabaseManagement\Util\Helper;

use NewDavis\DatabaseManagement\Entity\EntityDefinitionInterface;
use NewDavis\DatabaseManagement\Entity\EntityRegistry;
use NewDavis\DatabaseManagement\Entity\Field\FieldCollection;
use NewDavis\DatabaseManagement\Entity\Field\Flag\ConstraintActions;
use NewDavis\DatabaseManagement\Entity\Field\Flag\OnDelete;
use NewDavis\DatabaseManagement\Entity\Field\Relational\FkField;
use NewDavis\DatabaseManagement\Entity\Field\Relational\ManyToManyRelation;

class EntityTableHelper
{
    public static function buildMappingTableName(
        EntityDefinitionInterface $definition,
        EntityRegistry $registry,
        ManyToManyRelation $relation
    ): string {
        if ($relation->getMappingTableName() != null) {
            return $relation->getMappingTableName();
        }

        $currentTableName = $definition->getEntityName();
        $relatedTableName = $registry->getDefinitionByDefinitionClass(
            $relation->getRelatedToDefinition()
        )->getEntityName();

        $sorted = [
            $currentTableName,
            $relatedTableName
        ];
        sort($sorted);

        return "{$sorted[0]}_{$sorted[1]}";
    }

    public static function buildMappingTableFields(
        EntityDefinitionInterface $definition,
        EntityRegistry $registry,
        ManyToManyRelation $manyToManyField,
        ?string $mappingEntityName = null
    ): FieldCollection {
        $relatedDefinition = $registry->getDefinitionByDefinitionClass(
            $manyToManyField->getRelatedToDefinition()
        );

        $fieldDataSets = [
            [
                'table' => $definition->getEntityName(),
                'definition' => $definition::class,
                'property' => $manyToManyField->getRelatedByInternalName(),
            ],
            [
                'table' => $relatedDefinition->getEntityName(),
                'definition' => $relatedDefinition::class,
                'property' => $manyToManyField->getRelatedToInternalName(),
            ]
        ];
        usort($fieldDataSets, function ($a, $b) {
            return $a['table'] <=> $b['table'];
        });

        $fields = [];
        foreach ($fieldDataSets as $fieldData) {
            $fields[] = new FkField(
                StringHelper::toCamelCase("{$fieldData['table']}_{$fieldData['property']}"),
                "{$fieldData['table']}_{$fieldData['property']}",
                $fieldData['definition'],
                $fieldData['property'],
                [
                    new OnDelete(ConstraintActions::CASCADE),
                ]
            );
        }

        return new FieldCollection($fields, $mappingEntityName);
    }
}