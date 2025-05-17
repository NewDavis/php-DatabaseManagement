<?php

namespace NewDavis\DatabaseManagement\Test\Account;

use NewDavis\DatabaseManagement\Core\Entity\EntityDefinition;
use NewDavis\DatabaseManagement\Core\Entity\Field\AutoIncrementField;
use NewDavis\DatabaseManagement\Core\Entity\Field\BooleanField;
use NewDavis\DatabaseManagement\Core\Entity\Field\DateTimeField;
use NewDavis\DatabaseManagement\Core\Entity\Field\FkField;
use NewDavis\DatabaseManagement\Core\Entity\Field\IdField;
use NewDavis\DatabaseManagement\Core\Entity\Field\JSONField;
use NewDavis\DatabaseManagement\Core\Entity\Field\Relation\ManyToManyRelation;
use NewDavis\DatabaseManagement\Core\Entity\Field\Relation\ManyToOneRelation;
use NewDavis\DatabaseManagement\Core\Entity\Field\Relation\OneToOneRelation;
use NewDavis\DatabaseManagement\Core\Entity\Field\TextField;
use NewDavis\DatabaseManagement\Core\Entity\Flag\Required;
use NewDavis\DatabaseManagement\Test\Role\RoleDefinition;

class AccountDefinition implements EntityDefinition
{

    static function getEntityName(): string
    {
        return "account";
    }

    static function getEntityClass(): string
    {
        return AccountEntity::class;
    }

    static function getCollectionClass(): string
    {
        return AccountCollection::class;
    }

    static function getDefinitionFields(): array
    {
        return [
            new IdField('id', 'id'),
            new AutoIncrementField(),
            new TextField('username', 255, 'username', new Required()),
            new FkField('primaryRoleId', 'primary_role_id', RoleDefinition::class),
            new BooleanField('admin', 'admin', new Required()),
            new JSONField('customFields', 'custom_fields'),
            new DateTimeField('createdAt', 'created_at', new Required()),
            new DateTimeField('updatedAt', 'updated_at'),

            new OneToOneRelation(
                'primaryRole',
                'primary_role_id',
                RoleDefinition::class,
                'id',
                false
            ),

            new ManyToManyRelation(
                'roles',
                RoleDefinition::class,
                'id',
                'id',
                false
            )
        ];
    }
}