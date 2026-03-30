<?php

namespace NewDavis\DatabaseManagement\Entity;

use NewDavis\DatabaseManagement\Entity\Write\EntityWriteStatement;

/**
 * @template T
 */
class AbstractStatementCollection implements \IteratorAggregate, \Countable
{
    /**
     * @param array<T> $statements
     */
    public function __construct(
        private array $statements
    ) {
    }

    /**
     * @param EntityWriteStatement $statement
     * @return void
     */
    public function add(EntityWriteStatement $statement): void
    {
        $this->statements[] = $statement;
    }

    /**
     * @return array<T>
     */
    public function getStatements(): array
    {
        return $this->statements;
    }

    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->statements);
    }

    public function count(): int
    {
        return count($this->statements);
    }
}
