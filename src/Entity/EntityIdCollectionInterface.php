<?php

namespace NewDavis\DatabaseManagement\Entity;

use Ramsey\Uuid\UuidInterface;

interface EntityIdCollectionInterface extends \IteratorAggregate, \Countable
{
    public function add(AbstractEntity|UuidInterface $entity): void;
    public function remove(AbstractEntity|UuidInterface $entity): void;
    public function has(AbstractEntity|UuidInterface $entity): bool;
    public function indexAt(int $index): ?UuidInterface;
    public function indexOf(AbstractEntity|UuidInterface $entity): false|int|string;
    public function first(): ?UuidInterface;
    public function getIds(): array;
}