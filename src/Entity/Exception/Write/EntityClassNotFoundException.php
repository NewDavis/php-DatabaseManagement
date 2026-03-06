<?php

namespace NewDavis\DatabaseManagement\Entity\Exception\Write;

class EntityClassNotFoundException extends \Exception
{
    public function __construct(string $entityClass)
    {
        parent::__construct("The entity class \"{$entityClass}\" does not exist.");
    }
}