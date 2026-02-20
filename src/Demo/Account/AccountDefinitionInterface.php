<?php

namespace NewDavis\DatabaseManagement\Demo\Account;

use NewDavis\DatabaseManagement\Demo\Role\RoleDefinition;
use NewDavis\DatabaseManagement\Demo\Token\TokenDefinition;
use NewDavis\DatabaseManagement\Entity\EntityDefinitionInterface;
use NewDavis\DatabaseManagement\Entity\Field\Flag\Required;
use NewDavis\DatabaseManagement\Entity\Field\Flag\Unique;
use NewDavis\DatabaseManagement\Entity\Field\Relational\FkField;
use NewDavis\DatabaseManagement\Entity\Field\Relational\ManyToManyRelation;
use NewDavis\DatabaseManagement\Entity\Field\Relational\ManyToOneRelation;
use NewDavis\DatabaseManagement\Entity\Field\Relational\OneToManyRelation;
use NewDavis\DatabaseManagement\Entity\Field\Relational\OneToOneRelation;
use NewDavis\DatabaseManagement\Entity\Field\Scalar\AutoIncrementField;
use NewDavis\DatabaseManagement\Entity\Field\Scalar\Convertable\PasswordField;
use NewDavis\DatabaseManagement\Entity\Field\Scalar\IdField;
use NewDavis\DatabaseManagement\Entity\Field\Scalar\StringField;

class AccountDefinitionInterface implements EntityDefinitionInterface
{
    public static function getEntityName(): string
    {
        return 'account';
    }

    public static function getFields(): array
    {
        return [
            new IdField(),
            new AutoIncrementField(),

            new StringField('username', 'username', 255, [new Required(), new Unique()]),
            new StringField('email', 'email', 255, [new Required(), new Unique()]),
            new PasswordField('password', 'password', [new Required(), new Unique()]),

            new ManyToManyRelation('roles', RoleDefinition::class, 'id', 'id', true),

            new FkField('primaryRoleId', 'primary_role_id', RoleDefinition::class, [new Required()]),
            new ManyToOneRelation('primaryRole', 'primary_role_id', RoleDefinition::class, 'id', 'id', true),

            new FkField('tokenId', 'token_id', TokenDefinition::class),
            new OneToOneRelation('token', 'token', TokenDefinition::class, 'id', 'id', true),

            new OneToManyRelation('follower', AccountDefinitionInterface::class, 'id', 'id', false),
        ];
    }

    public static function getEntityClass(): string
    {
        return AccountEntity::class;
    }

    public static function getCollectionClass(): string
    {
        return AccountCollection::class;
    }
}