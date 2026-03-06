<?php

namespace NewDavis\DatabaseManagement\Demo\Account;

use NewDavis\DatabaseManagement\Demo\Role\RoleDefinition;
use NewDavis\DatabaseManagement\Demo\Token\TokenDefinition;
use NewDavis\DatabaseManagement\Entity\EntityDefinitionInterface;
use NewDavis\DatabaseManagement\Entity\Field\Flag\CaseSensitive;
use NewDavis\DatabaseManagement\Entity\Field\Flag\FlagTypeCollection;
use NewDavis\DatabaseManagement\Entity\Field\Flag\Required;
use NewDavis\DatabaseManagement\Entity\Field\Flag\Unique;
use NewDavis\DatabaseManagement\Entity\Field\Relational\FkField;
use NewDavis\DatabaseManagement\Entity\Field\Relational\ManyToManyRelation;
use NewDavis\DatabaseManagement\Entity\Field\Relational\ManyToOneRelation;
use NewDavis\DatabaseManagement\Entity\Field\Relational\OneToManyRelation;
use NewDavis\DatabaseManagement\Entity\Field\Relational\OneToOneRelation;
use NewDavis\DatabaseManagement\Entity\Field\Scalar\AutoIncrementField;
use NewDavis\DatabaseManagement\Entity\Field\Scalar\Convertable\PasswordField;
use NewDavis\DatabaseManagement\Entity\Field\Scalar\CreatedAtField;
use NewDavis\DatabaseManagement\Entity\Field\Scalar\IdField;
use NewDavis\DatabaseManagement\Entity\Field\Scalar\StringField;
use NewDavis\DatabaseManagement\Entity\Field\Scalar\UpdatedAtField;
use NewDavis\DatabaseManagement\Entity\FieldCollection;

class AccountDefinition implements EntityDefinitionInterface
{
    public function getEntityName(): string
    {
        return 'account';
    }

    public function getFields(): FieldCollection
    {
        return new FieldCollection([
            new IdField(),
            new AutoIncrementField(),

            new StringField('username', 'username', 255, [new CaseSensitive(), new Required(), new Unique()]),
            new StringField('email', 'email', 255, [new Required(), new Unique()]),
            new PasswordField('password', 'password', [new Required()]),

            new ManyToManyRelation('roles', RoleDefinition::class, 'id', 'id', null, true),

            new FkField('primaryRoleId', 'primary_role_id', RoleDefinition::class, 'id', [new Required()]),
            new ManyToOneRelation('primaryRole', 'primary_role_id', RoleDefinition::class, 'id', true),

            new FkField('tokenId', 'token_id', TokenDefinition::class),
            new OneToOneRelation('token', 'token_id', TokenDefinition::class, 'id', true),

            new OneToManyRelation('follower', AccountDefinition::class, 'id', false),

            new CreatedAtField(),
            new UpdatedAtField(),
        ], self::getEntityName());
    }

    public function getEntityClass(): string
    {
        return AccountEntity::class;
    }

    public function getCollectionClass(): string
    {
        return AccountCollection::class;
    }
}
