<?php

namespace NewDavis\DatabaseManagement\Entity;

interface EntityCollectionInterface extends \IteratorAggregate, \Countable
{
    public function add(AbstractEntity $entity): void;
    public function remove(AbstractEntity $entity): void;
    public function indexAt(int $index): ?AbstractEntity;
    public function indexOf(AbstractEntity $entity): false|int|string;
    public function first(): ?AbstractEntity;
    public function getEntities(): array;

    public static function getDefinitionClass(): string;
}