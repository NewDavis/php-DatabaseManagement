<?php

namespace NewDavis\DatabaseManagement\Entity;

/**
 * @template T
 */
class AbstractStatementCollection implements \IteratorAggregate, \Countable
{
    /**
     * @param array<T> $statements
     */
    public function __construct(
        private readonly array $statements
    ) {
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