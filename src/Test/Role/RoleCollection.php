<?php

namespace NewDavis\DatabaseManagement\Test\Role;

use NewDavis\DatabaseManagement\Core\Entity\EntityCollection;

/**
 * @extends EntityCollection<RoleEntity>
 */
class RoleCollection extends EntityCollection
{
    public static function getDefinitionClass(): string
    {
        return RoleDefinition::class;
    }
}