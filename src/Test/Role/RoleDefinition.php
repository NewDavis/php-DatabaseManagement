<?php

namespace NewDavis\DatabaseManagement\Test\Role;

use NewDavis\DatabaseManagement\Core\Entity\EntityDefinition;
use NewDavis\DatabaseManagement\Core\Entity\Field\DateTimeField;
use NewDavis\DatabaseManagement\Core\Entity\Field\IdField;
use NewDavis\DatabaseManagement\Core\Entity\Field\Relation\ManyToManyRelation;
use NewDavis\DatabaseManagement\Core\Entity\Field\Relation\ManyToOneRelation;
use NewDavis\DatabaseManagement\Core\Entity\Field\Relation\OneToManyRelation;
use NewDavis\DatabaseManagement\Core\Entity\Field\TextField;
use NewDavis\DatabaseManagement\Core\Entity\Flag\Required;
use NewDavis\DatabaseManagement\Test\Account\AccountDefinition;

class RoleDefinition implements EntityDefinition
{

    static function getEntityName(): string
    {
        return "role";
    }

    static function getEntityClass(): string
    {
        return RoleEntity::class;
    }

    static function getCollectionClass(): string
    {
        return RoleCollection::class;
    }

    static function getDefinitionFields(): array
    {
        return [
            new IdField('id', 'id'),
            new TextField('name', 255, 'name', new Required()),
            new DateTimeField('createdAt', 'created_at', new Required()),
            new DateTimeField('updatedAt', 'updated_at'),

            new OneToManyRelation(
                'accounts',
                RoleDefinition::class,
                'id',
                'role_id'
            ),
        ];
    }
}