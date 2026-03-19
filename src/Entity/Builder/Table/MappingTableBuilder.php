<?php

namespace NewDavis\DatabaseManagement\Entity\Builder\Table;

use NewDavis\DatabaseManagement\Entity\Field\Field;
use NewDavis\DatabaseManagement\Entity\Field\FieldCollection;
use NewDavis\DatabaseManagement\Entity\Field\Flag\ConstraintActions;
use NewDavis\DatabaseManagement\Entity\Field\Flag\OnDelete;
use NewDavis\DatabaseManagement\Entity\Field\Flag\Unique;
use NewDavis\DatabaseManagement\Entity\Field\Flag\UniqueConvertion;
use NewDavis\DatabaseManagement\Entity\Field\FlagField;
use NewDavis\DatabaseManagement\Entity\Field\Relational\FkField;
use NewDavis\DatabaseManagement\Entity\Field\Relational\ManyToManyRelation;
use NewDavis\DatabaseManagement\Entity\Field\StorableInterface;
use NewDavis\DatabaseManagement\Util\Helper\EntityTableHelper;
use NewDavis\DatabaseManagement\Util\Helper\StringHelper;

class MappingTableBuilder
{
    /** @var null|array<TableBuilder> */
    private ?array $mappingTables = null;

    public function __construct(
        private readonly TableBuilder $table,
    ) {
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
            $mappingEntityName = EntityTableHelper::buildMappingTableName(
                $this->table->getDefinition(),
                $this->table->getRegistry(),
                $manyToManyField
            );

            $mappingFields = EntityTableHelper::buildMappingTableFields(
                $this->table->getDefinition(),
                $this->table->getRegistry(),
                $manyToManyField,
                $mappingEntityName
            );

            $mappingFields->add(new FlagField([
                new Unique(
                    UniqueConvertion::MULTIPLE,
                    array_map(
                        fn(StorableInterface $storable) => $storable->getStorageName(),
                        $mappingFields->getFields()
                    )
                )
            ]));

            $this->mappingTables[] = new TableBuilder(
                $mappingEntityName,
                $mappingFields,
                $this->table->getRegistry()
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
