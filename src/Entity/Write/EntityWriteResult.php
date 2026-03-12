<?php

namespace NewDavis\DatabaseManagement\Entity\Write;

use NewDavis\DatabaseManagement\Entity\EntityCollectionInterface;

class EntityWriteResult
{
    /**
     * @param EntityCollectionInterface $entities
     * @param bool $success
     */
    public function __construct(
        private readonly EntityCollectionInterface $entities,
        private readonly bool $success = false
    ) {
    }

    /**
     * @return EntityCollectionInterface
     */
    public function getEntities(): EntityCollectionInterface
    {
        return $this->entities;
    }

    /**
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->success;
    }
}
