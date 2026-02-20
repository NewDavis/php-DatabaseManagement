<?php

namespace NewDavis\DatabaseManagement\Demo\Account;

use NewDavis\DatabaseManagement\Entity\AbstractEntity;

class AccountEntity extends AbstractEntity
{
    public static function getDefinitionClass(): string
    {
        return AccountDefinitionInterface::class;
    }
}