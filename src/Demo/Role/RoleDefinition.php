<?php

namespace NewDavis\DatabaseManagement\Demo\Role;

use NewDavis\DatabaseManagement\Entity\EntityDefinitionInterface;
use NewDavis\DatabaseManagement\Entity\Field\Flag\Required;
use NewDavis\DatabaseManagement\Entity\Field\Flag\Unique;
use NewDavis\DatabaseManagement\Entity\Field\Scalar\AutoIncrementField;
use NewDavis\DatabaseManagement\Entity\Field\Scalar\IdField;
use NewDavis\DatabaseManagement\Entity\Field\Scalar\StringField;
use NewDavis\DatabaseManagement\Entity\FieldCollection;

class RoleDefinition implements EntityDefinitionInterface
{
    public static function getEntityName(): string
    {
        return 'role';
    }

    public static function getFields(): FieldCollection
    {
        return new FieldCollection([
            new IdField(),
            new AutoIncrementField(),

            new StringField('name', 'name', 255, [new Required(), new Unique()])
        ], self::getEntityName());
    }

    public static function getEntityClass(): string
    {
        return RoleEntity::class;
    }

    public static function getCollectionClass(): string
    {
        return RoleCollection::class;
    }
}
