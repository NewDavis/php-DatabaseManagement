<?php

namespace NewDavis\DatabaseManagement\Demo\Role;

use NewDavis\DatabaseManagement\Entity\AbstractEntity;

class RoleEntity extends AbstractEntity
{
    public static function getDefinitionClass(): string
    {
        return RoleDefinition::class;
    }
}