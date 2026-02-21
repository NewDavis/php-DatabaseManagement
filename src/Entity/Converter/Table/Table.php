<?php

namespace NewDavis\DatabaseManagement\Entity\Converter\Table;

use NewDavis\DatabaseManagement\Entity\Field\Field;
use NewDavis\DatabaseManagement\Entity\Field\Flag\Flag;
use NewDavis\DatabaseManagement\Entity\Field\Flag\PrimaryKey;
use NewDavis\DatabaseManagement\Entity\Field\StorableInterface;
use NewDavis\DatabaseManagement\Entity\Field\SupportsFlags;

class Table
{
    /** @var string */
    private string $tableName;

    /** @var array<Field> */
    private array $definedFields = [];

    /** @var array<string> */
    private array $primaryKeys = [];

    /** @var array<string> */
    private array $foreignKeys = [];

    public function __construct(string $definitionClass)
    {
        $this->tableName = $definitionClass::getEntityName();
        $this->definedFields = $definitionClass::getFields();

        $this->extractPrimaryKeys();
    }

    private function extractPrimaryKeys(): void
    {
        foreach ($this->definedFields as $field) {
            if (
                !$field instanceof SupportsFlags ||
                !$field instanceof StorableInterface
            ) continue;

            $isPrimaryKey = array_filter(
                $field->getFlags(),
                fn (Flag $flag) => $flag instanceof PrimaryKey
            );

            if (!$isPrimaryKey) continue;

            $this->primaryKeys[] = $field->getStorageName();
        }
    }

    private function extractForeignKeys(): void
    {

    }
}