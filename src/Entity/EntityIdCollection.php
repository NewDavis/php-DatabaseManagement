<?php

namespace NewDavis\DatabaseManagement\Entity;

use Ramsey\Uuid\UuidInterface;
use Traversable;

class EntityIdCollection implements EntityIdCollectionInterface
{
    public function __construct(
        private array $ids = []
    ) {
    }

    public function add(AbstractEntity|UuidInterface $entity): void
    {
        $this->ids[] = $this->getId($entity);
    }

    public function addAll(EntityIdCollection|array $entities): void
    {
        foreach ($entities as $entity) {
            $this->add($entity);
        }
    }

    public function remove(AbstractEntity|UuidInterface $entity): void
    {
        $index = $this->indexOf($entity);

        if ($index === false) return;

        unset($this->ids[$index]);
    }

    public function clear(): void
    {
        $this->ids = [];
    }

    public function has(UuidInterface|AbstractEntity $entity): bool
    {
        return in_array($this->getId($entity), $this->ids);
    }

    public function indexAt(int $index): ?UuidInterface
    {
        return $this->ids[$index] ?? null;
    }

    public function indexOf(AbstractEntity|UuidInterface $entity): false|int|string
    {
        return array_search(
            $this->getId($entity),
            array_values($this->ids),
            true
        );
    }

    public function first(): ?UuidInterface
    {
        return $this->indexAt(0);
    }

    /**
     * @return array<UuidInterface>
     */
    public function getIds(): array
    {
        return $this->ids;
    }

    public function getIterator(): Traversable
    {
        return new \ArrayIterator($this->ids);
    }

    public function count(): int
    {
        return count($this->ids);
    }

    private function getId(AbstractEntity|UuidInterface $entity): UuidInterface
    {
        return $entity instanceof EntityInterface ? $entity->getId()->toString() : $entity;
    }
}
