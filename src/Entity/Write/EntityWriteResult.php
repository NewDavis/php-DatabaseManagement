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
        private readonly EntityWriteStatementCollection $statements,
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
     * @return EntityWriteStatementCollection
     */
    public function getStatements(): EntityWriteStatementCollection
    {
        return $this->statements;
    }

    /**
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->success;
    }
}
