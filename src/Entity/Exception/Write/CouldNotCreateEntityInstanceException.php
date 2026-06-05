<?php

namespace NewDavis\DatabaseManagement\Entity\Exception\Write;

use NewDavis\DatabaseManagement\Entity\EntityDefinitionInterface;

class CouldNotCreateEntityInstanceException extends \Exception
{
    public function __construct(EntityDefinitionInterface $definition)
    {
        parent::__construct(
            "Couldn`t create entity instance for {$definition->getEntityClass()}.",
        );
    }
}
