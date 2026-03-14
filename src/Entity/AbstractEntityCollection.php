<?php

namespace NewDavis\DatabaseManagement\Entity;

abstract class AbstractEntityCollection implements EntityCollectionInterface
{
    public function __construct(
        private array $entities = []
    ) {
    }

    public function count(): int
    {
        return count($this->entities);
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
}
