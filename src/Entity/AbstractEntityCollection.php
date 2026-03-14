<?php

namespace NewDavis\DatabaseManagement\Entity;

use Traversable;

abstract class AbstractEntityCollection implements EntityCollectionInterface
{
    public function __construct(
        private array $entities = []
    ) {
    }

    public function add(EntityInterface $entity): void
    {
        $this->entities[] = $entity;
    }

    public function getIndex(int $index): ?AbstractEntity
    {
        return $this->entities[$index] ?? null;
    }

    /**
     * @return array
     */
    public function getEntities(): array
    {
        return $this->entities;
    }

    public function getIterator(): Traversable
    {
        return new \ArrayIterator($this->entities);
    }

    public function count(): int
    {
        return count($this->entities);
    }
}
