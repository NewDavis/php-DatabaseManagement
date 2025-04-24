<?php

namespace NewDavis\DatabaseManagement\Test\Account;

use NewDavis\DatabaseManagement\Core\Entity\Entity;
use NewDavis\DatabaseManagement\Core\Entity\Trait\AutoIncrementTrait;

class AccountEntity extends Entity
{
    use AutoIncrementTrait;

    private ?string $username;
    private ?bool $admin;
    private ?array $customFields;

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