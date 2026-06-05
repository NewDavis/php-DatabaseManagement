<?php

namespace NewDavis\DatabaseManagement\Demo\Token;

use NewDavis\DatabaseManagement\Demo\Account\AccountEntity;
use NewDavis\DatabaseManagement\Entity\AbstractEntity;
use NewDavis\DatabaseManagement\Entity\Trait\EntityIdTrait;
use Ramsey\Uuid\UuidInterface;

class TokenEntity extends AbstractEntity
{
    use EntityIdTrait;

    protected string $token;

    protected UuidInterface $accountId;
    protected ?AccountEntity $account;

    public static function getDefinitionClass(): string
    {
        return TokenDefinition::class;
    }
}
