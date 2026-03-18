<?php

namespace NewDavis\DatabaseManagement\Entity;

use Traversable;

abstract class AbstractEntityCollection implements EntityCollectionInterface
{
    public function __construct(
        private array $entities = []
    ) {
    }

    public function add(AbstractEntity $entity): void
    {
        $this->entities[$entity->getId()->toString()] = $entity;
    }

    public function remove(AbstractEntity $entity): void
    {
        if (!array_key_exists($entity->getId()->toString(), $this->entities)) return;

        unset($this->entities[$entity->getId()->toString()]);
    }

    public function indexAt(int $index): ?AbstractEntity
    {
        $idIndex = array_keys($this->entities)[$index];

        return $this->entities[$idIndex] ?? null;
    }

    public function indexOf(AbstractEntity $entity): false|int|string
    {
        return array_search(
            $entity->getId()->toString(),
            array_keys($this->entities),
            true
        );
    }

    public function first(): ?AbstractEntity
    {
        return $this->indexAt(0);
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
