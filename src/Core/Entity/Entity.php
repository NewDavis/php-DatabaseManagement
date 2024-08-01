<?php

namespace DatabaseManagement\Core\Entity;

use DatabaseManagement\Core\Entity\Trait\CreatedAtTrait;
use DatabaseManagement\Core\Entity\Trait\IdTrait;
use DatabaseManagement\Core\Entity\Trait\UpdatedAtTrait;
use Ramsey\Uuid\Uuid;

class Entity implements EntityInterface
{

    private bool $persisted;
    private bool $delete = false;

    public function __construct(bool $persisted = false)
    {
        $this->persisted = $persisted;
        $this->id = Uuid::uuid4()->toString();
        $this->created_at = round(1000 * microtime(true));
        $this->updated_at = round(1000 * microtime(true));
    }

    use IdTrait;
    use CreatedAtTrait;
    use UpdatedAtTrait;

    public function isPersisted(): bool
    {
        return $this->persisted;
    }

    public function setShouldDelete(bool $shouldDelete): bool
    {
        return $this->delete = $shouldDelete;
    }

    public function shouldDelete(): bool
    {
        return $this->delete;
    }

    public function getDefinitionClass(): string|null
    {
        return null;
    }

    public function jsonSerialize(): array
    {
        $properties = [];

        unset($properties['persisted']);
        unset($properties['delete']);

        return $properties;
    }

}