<?php

namespace NewDavis\DatabaseManagement\Entity;

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

    /**
     * @return array
     */
    public function getEntities(): array
    {
        return $this->entities;
    }
}
