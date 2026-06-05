<?php

namespace NewDavis\DatabaseManagement\Demo\Role;

use NewDavis\DatabaseManagement\Entity\AbstractEntity;
use NewDavis\DatabaseManagement\Entity\Trait\EntityAutoIncrementTrait;
use NewDavis\DatabaseManagement\Entity\Trait\EntityIdTrait;

class RoleEntity extends AbstractEntity
{
    use EntityIdTrait;
    use EntityAutoIncrementTrait;

    protected string $name;

    public static function getDefinitionClass(): string
    {
        return RoleDefinition::class;
    }
}
