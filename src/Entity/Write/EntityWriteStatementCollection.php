<?php

namespace NewDavis\DatabaseManagement\Entity\Write;

class EntityWriteStatementCollection
{
    /**
     * @param array<EntityWriteStatement> $statements
     */
    public function __construct(
        private readonly array $statements
    ) {
    }

    public function count(): int
    {
        return count($this->statements);
    }

    /**
     * @return array
     */
    public function getStatements(): array
    {
        return $this->statements;
    }
}