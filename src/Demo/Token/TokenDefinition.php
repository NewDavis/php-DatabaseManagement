<?php

namespace NewDavis\DatabaseManagement\Demo\Token;

use NewDavis\DatabaseManagement\Demo\Account\AccountDefinition;
use NewDavis\DatabaseManagement\Entity\EntityDefinitionInterface;
use NewDavis\DatabaseManagement\Entity\Field\Field;
use NewDavis\DatabaseManagement\Entity\Field\Flag\ConstraintActions;
use NewDavis\DatabaseManagement\Entity\Field\Flag\OnDelete;
use NewDavis\DatabaseManagement\Entity\Field\Flag\Required;
use NewDavis\DatabaseManagement\Entity\Field\Flag\Unique;
use NewDavis\DatabaseManagement\Entity\Field\Relational\FkField;
use NewDavis\DatabaseManagement\Entity\Field\Relational\OneToOneRelation;
use NewDavis\DatabaseManagement\Entity\Field\Scalar\IdField;
use NewDavis\DatabaseManagement\Entity\Field\Scalar\StringField;
use NewDavis\DatabaseManagement\Entity\FieldCollection;

class TokenDefinition implements EntityDefinitionInterface
{
    public static function getEntityName(): string
    {
        return 'token';
    }

    public static function getFields(): FieldCollection
    {
        return new FieldCollection([
            new IdField(),

            new StringField('token', 'token', 255, [new Required(), new Unique()]),

            new FkField('accountId', 'account_id', AccountDefinition::class, 'id', [new Required(), new OnDelete(ConstraintActions::CASCADE)]),
            new OneToOneRelation('account', 'account_id', AccountDefinition::class, 'id', false),
        ], self::getEntityName());
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
