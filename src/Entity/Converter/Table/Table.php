<?php

namespace NewDavis\DatabaseManagement\Entity\Converter\Table;

use NewDavis\DatabaseManagement\Entity\Field\Field;

class Table
{
    /** @var string */
    private string $tableName;
    /** @var array<Field> */
    private array $definedFields;

    public function __construct(string $definitionClass)
    {

    }
}