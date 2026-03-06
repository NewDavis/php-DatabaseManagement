<?php

namespace NewDavis\DatabaseManagement\Entity\Exception\Write;

class
EntityDefinitionMissesEntityClassMethodException extends \Exception
{
    public function __construct(string $definitionClass)
    {
        parent::__construct(
            "The definition {$definitionClass} has no getEntityClass() method."
        );
    }
}