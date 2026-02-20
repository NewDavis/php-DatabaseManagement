<?php

namespace NewDavis\DatabaseManagement\Demo\Account;

use NewDavis\DatabaseManagement\Entity\AbstractEntityCollection;

class AccountCollection extends AbstractEntityCollection
{
    public static function getDefinitionClass(): string
    {
        return AccountDefinitionInterface::class;
    }
}