<?php

namespace NewDavis\DatabaseManagement\Demo\Role;

use NewDavis\DatabaseManagement\Entity\AbstractEntityCollection;

class RoleCollection extends AbstractEntityCollection
{
    public static function getDefinitionClass(): string
    {
        return RoleDefinition::class;
    }
}