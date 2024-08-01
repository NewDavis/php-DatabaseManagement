<?php

namespace NewDavis\DatabaseManagement\Core\Entity;

use DateTimeImmutable;
use NewDavis\DatabaseManagement\Core\Entity\Trait\CreatedAtTrait;
use NewDavis\DatabaseManagement\Core\Entity\Trait\IdTrait;
use NewDavis\DatabaseManagement\Core\Entity\Trait\UpdatedAtTrait;
use Ramsey\Uuid\Uuid;

class Entity
{

    private bool $persisted;
    private bool $delete = false;

    public function __construct(bool $persisted = false)
    {
        $this->persisted = $persisted;
        $this->id = Uuid::uuid4()->toString();
        $this->created_at = new DateTimeImmutable();
        $this->updated_at = new DateTimeImmutable();
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

    public function jsonSerialize(bool $subSerialize = true, int $nestingDepth = 0, int $maxNestingDepth = 10): array
    {
        $properties = [];

        unset($properties['persisted']);
        unset($properties['delete']);

        return $properties;
    }

}