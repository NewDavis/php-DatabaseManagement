<?php

namespace NewDavis\DatabaseManagement\Entity\Builder\Table;

use NewDavis\DatabaseManagement\Entity\Field\Field;
use NewDavis\DatabaseManagement\Entity\Field\Flag\ConstraintActions;
use NewDavis\DatabaseManagement\Entity\Field\Flag\OnDelete;
use NewDavis\DatabaseManagement\Entity\Field\Flag\Unique;
use NewDavis\DatabaseManagement\Entity\Field\Flag\UniqueConvertion;
use NewDavis\DatabaseManagement\Entity\Field\FlagField;
use NewDavis\DatabaseManagement\Entity\Field\Relational\FkField;
use NewDavis\DatabaseManagement\Entity\Field\Relational\ManyToManyRelation;
use NewDavis\DatabaseManagement\Entity\FieldCollection;
use NewDavis\DatabaseManagement\Util\Helper\StringHelper;

class MappingTableBuilder
{
    /** @var null|array<TableBuilder> */
    private ?array $mappingTables = null;

    public function __construct(
        private readonly TableBuilder $table,
    ) {
    }

    private function buildMappingTableName(ManyToManyRelation $relation): string
    {
        if ($relation->getMappingTableName() != null) {
            return $relation->getMappingTableName();
        }

        $currentTableName = $this->table->getTableName();
        $relatedTableName = $relation->getRelatedToDefinition()::getEntityName();

        $sorted = [
            $currentTableName,
            $relatedTableName
        ];
        sort($sorted);

        return "{$sorted[0]}_{$sorted[1]}";
    }

    private function createMappingTables(): void
    {
        if ($this->table->getDefinition() == null) {
            return;
        }

        /** @var ManyToManyRelation $manyToManyField */
        foreach (
            array_filter(
                $this->table->getDefinedFields()->getFields(),
                fn (Field $field) => $field instanceof ManyToManyRelation
            )
            as $manyToManyField
        ) {
            $fieldDataSets = [
                [
                    'table' => $this->table->getTableName(),
                    'definition' => $this->table->getDefinition(),
                    'property' => $manyToManyField->getRelatedByInternalName(),
                ],
                [
                    'table' => $manyToManyField->getRelatedToDefinition()::getEntityName(),
                    'definition' => $this->table->getDefinedFields()->getRelatedDefinition($manyToManyField),
                    'property' => $manyToManyField->getRelatedToInternalName(),
                ]
            ];
            usort($fieldDataSets, function ($a, $b) {
                return $a['table'] <=> $b['table'];
            });

            $keys = [];
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

                $keys[] = "{$fieldData['table']}_{$fieldData['property']}";
            }

            $fields[] = new FlagField([
                new Unique(
                    UniqueConvertion::MULTIPLE,
                    $keys
                )
            ]);

            $mappingEntityName = $this->buildMappingTableName($manyToManyField);
            $mappingFields = new FieldCollection($fields, $mappingEntityName);

            $this->mappingTables[] = new TableBuilder(
                $mappingEntityName,
                $mappingFields
            );
        }
    }

    /**
     * @return array
     */
    public function getMappingTables(): array
    {
        if ($this->mappingTables == null) {
            $this->mappingTables = [];
            $this->createMappingTables();
        }

        return $this->mappingTables;
    }
}