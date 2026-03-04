<?php

namespace NewDavis\DatabaseManagement\Entity\Exception;

class NoDefinitionFoundForMappingCreationException extends \Exception
{
    public function __construct(string $tableName)
    {
        parent::__construct("There is no definition found for the current mapping of {$tableName}.");
    }
}