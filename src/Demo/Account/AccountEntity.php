<?php

namespace NewDavis\DatabaseManagement\Demo\Account;

use NewDavis\DatabaseManagement\Demo\Role\RoleCollection;
use NewDavis\DatabaseManagement\Demo\Role\RoleEntity;
use NewDavis\DatabaseManagement\Demo\Token\TokenEntity;
use NewDavis\DatabaseManagement\Entity\AbstractEntity;
use NewDavis\DatabaseManagement\Entity\Trait\EntityAutoIncrementTrait;
use NewDavis\DatabaseManagement\Entity\Trait\EntityCreatedAtTrait;
use NewDavis\DatabaseManagement\Entity\Trait\EntityIdTrait;
use NewDavis\DatabaseManagement\Entity\Trait\EntityUpdatedAtTrait;
use Ramsey\Uuid\Uuid;

class AccountEntity extends AbstractEntity
{
    public function __construct()
    {
        parent::__construct();

        $this->roles = new RoleCollection();
    }

    use EntityIdTrait;
    use EntityAutoIncrementTrait;

    protected string $username;
    protected string $email;
    protected string $password;

    protected Uuid $primaryRoleId;
    protected ?RoleEntity $primaryRole;

    protected RoleCollection $roles;

    protected Uuid $tokenId;
    protected ?TokenEntity $token;

    use EntityCreatedAtTrait;
    use EntityUpdatedAtTrait;

    public static function getDefinitionClass(): string
    {
        return AccountDefinition::class;
    }
}
