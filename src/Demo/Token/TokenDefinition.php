<?php

namespace NewDavis\DatabaseManagement\Demo\Token;

use NewDavis\DatabaseManagement\Demo\Account\AccountDefinitionInterface;
use NewDavis\DatabaseManagement\Entity\EntityDefinitionInterface;
use NewDavis\DatabaseManagement\Entity\Field\Field;
use NewDavis\DatabaseManagement\Entity\Field\Flag\Required;
use NewDavis\DatabaseManagement\Entity\Field\Flag\Unique;
use NewDavis\DatabaseManagement\Entity\Field\Relational\FkField;
use NewDavis\DatabaseManagement\Entity\Field\Relational\OneToOneRelation;
use NewDavis\DatabaseManagement\Entity\Field\Scalar\IdField;
use NewDavis\DatabaseManagement\Entity\Field\Scalar\StringField;

class TokenDefinition implements EntityDefinitionInterface
{
    public static function getEntityName(): string
    {
        return 'token';
    }

    public static function getFields(): array
    {
        return [
            new IdField(),

            new StringField('token', 'token', 255, [new Required(), new Unique()]),

            new FkField('accountId', 'account_id', AccountDefinitionInterface::class, [new Required()]),
            new OneToOneRelation('account', 'account_id', AccountDefinitionInterface::class, 'id', 'id', false),
        ];
    }

    public static function getEntityClass(): string
    {
        return TokenEntity::class;
    }

    public static function getCollectionClass(): string
    {
        return TokenCollection::class;
    }
}