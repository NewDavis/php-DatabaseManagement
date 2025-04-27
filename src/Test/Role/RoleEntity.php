<?php

namespace NewDavis\DatabaseManagement\Test\Role;

use NewDavis\DatabaseManagement\Core\Entity\Entity;
use NewDavis\DatabaseManagement\Test\Account\AccountCollection;

class RoleEntity extends Entity
{
    public function __construct()
    {
        parent::__construct();

        $this->accounts = new AccountCollection();
    }

    private ?string $name;
    private AccountCollection $accounts;

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     */
    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    static function getDefinitionClass(): string
    {
        return RoleDefinition::class;
    }
}