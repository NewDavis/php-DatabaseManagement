<?php

namespace NewDavis\DatabaseManagement\Demo\Account;

use NewDavis\DatabaseManagement\Entity\EntityDefinition;

class AccountDefinition implements EntityDefinition
{
    public static function getEntityName(): string
    {
        return 'account';
    }

    public function getFields(): array
    {
        return [

        ];
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