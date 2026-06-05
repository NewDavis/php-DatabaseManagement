<?php

namespace NewDavis\DatabaseManagement\Entity\Exception\Table;

class FieldNotFoundException extends \Exception
{
    public function __construct(string $tableName, string $internalName)
    {
        parent::__construct(
            "There is no field in {$tableName} with internalName {$internalName}"
        );
    }
}