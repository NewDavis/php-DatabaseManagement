<?php

namespace NewDavis\DatabaseManagement\Entity\Write;

use Countable;
use Traversable;

class EntityWriteStatementCollection implements \IteratorAggregate, Countable
{
    /**
     * @param array<EntityWriteStatement> $statements
     */
    public function __construct(
        private readonly array $statements
    ) {
    }

    /**
     * @return array
     */
    public function getStatements(): array
    {
        return $this->statements;
    }

    public function getIterator(): Traversable
    {
        return new \ArrayIterator($this->statements);
    }

    public function count(): int
    {
        return count($this->statements);
    }
}