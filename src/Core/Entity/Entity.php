<?php

namespace NewDavis\DatabaseManagement\Core\Entity;

use NewDavis\DatabaseManagement\Core\Entity\Trait\CreatedAtTrait;
use NewDavis\DatabaseManagement\Core\Entity\Trait\IdTrait;
use NewDavis\DatabaseManagement\Core\Entity\Trait\UpdatedAtTrait;
use Ramsey\Uuid\Uuid;

abstract class Entity implements EntityInterface
{
    private bool $persisted;
    private bool $delete = false;

    public function __construct(bool $persisted = false)
    {
        $this->persisted = $persisted;
        $this->id = Uuid::uuid4()->toString();
        $this->createdAt = new \DateTimeImmutable();
    }

    use IdTrait;
    use CreatedAtTrait;
    use UpdatedAtTrait;

    public function isPersisted(): bool
    {
        return $this->persisted;
    }

    public function setDelete(bool $delete): bool
    {
        return $this->delete = $delete;
    }

    public function getDelete(): bool
    {
        return $this->delete;
    }

    public function jsonSerialize(): array
    {
        return [];
    }
}