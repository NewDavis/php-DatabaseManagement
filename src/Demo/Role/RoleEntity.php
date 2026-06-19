<?php

namespace NewDavis\DatabaseManagement\Demo\Role;

use NewDavis\DatabaseManagement\Demo\Account\AccountCollection;
use NewDavis\DatabaseManagement\Entity\AbstractEntity;
use NewDavis\DatabaseManagement\Entity\Trait\EntityAutoIncrementTrait;
use NewDavis\DatabaseManagement\Entity\Trait\EntityIdTrait;

class RoleEntity extends AbstractEntity
{
    use EntityIdTrait;
    use EntityAutoIncrementTrait;

    public function __construct()
    {
        $this->primaryUsage = new AccountCollection();
    }

    protected string $name;

    protected AccountCollection $primaryUsage;

    public static function getDefinitionClass(): string
    {
        return RoleDefinition::class;
    }
}
