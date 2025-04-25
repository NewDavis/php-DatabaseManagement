<?php

namespace NewDavis\DatabaseManagement\Core\Entity;

use NewDavis\DatabaseManagement\Core\Entity\Trait\CreatedAtTrait;
use NewDavis\DatabaseManagement\Core\Entity\Trait\IdTrait;
use NewDavis\DatabaseManagement\Core\Entity\Trait\UpdatedAtTrait;
use Ramsey\Uuid\Uuid;

abstract class Entity implements EntityInterface
{

    public function __construct()
    {
        $this->id = Uuid::uuid4()->toString();
        $this->createdAt = new \DateTimeImmutable();
    }

    use IdTrait;
    use CreatedAtTrait;
    use UpdatedAtTrait;

    public function jsonSerialize(): array
    {
        return [];
    }
}