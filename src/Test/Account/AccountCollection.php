<?php

namespace NewDavis\DatabaseManagement\Test\Account;

use NewDavis\DatabaseManagement\Core\Entity\EntityCollection;

/**
 * @extends EntityCollection<AccountEntity>
 */
class AccountCollection extends EntityCollection
{
    public static function getDefinitionClass(): ?string
    {
        return AccountDefinition::class;
    }
}