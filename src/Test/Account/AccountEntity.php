<?php

namespace NewDavis\DatabaseManagement\Test\Account;

use NewDavis\DatabaseManagement\Core\Entity\Entity;
use NewDavis\DatabaseManagement\Core\Entity\Trait\AutoIncrementTrait;
use NewDavis\DatabaseManagement\Test\Role\RoleCollection;
use NewDavis\DatabaseManagement\Test\Role\RoleEntity;

class AccountEntity extends Entity
{
    use AutoIncrementTrait;

    public function __construct()
    {
        parent::__construct();

        $this->roles = new RoleCollection();
    }

    private ?string $username;
    private ?bool $admin;
    private ?array $customFields;
    private string $primaryRoleId;
    private ?RoleEntity $primaryRole;
    private RoleCollection $roles;

    /**
     * @return string|null
     */
    public function getUsername(): ?string
    {
        return $this->username;
    }

    /**
     * @param string|null $username
     */
    public function setUsername(?string $username): void
    {
        $this->username = $username;
    }

    static function getDefinitionClass(): string
    {
        return AccountDefinition::class;
    }
}