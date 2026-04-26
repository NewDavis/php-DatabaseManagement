<?php

namespace NewDavis\DatabaseManagement\Entity\Builder\Condition;

use NewDavis\DatabaseManagement\Entity\Write\EntityWriteStatement;

class JoinCollection implements \IteratorAggregate, \Countable
{
    /**
     * @param array<Join> $joins
     */
    public function __construct(
        private array $joins
    ) {
    }

    /**
     * @param Join $join
     * @return void
     */
    public function add(Join $join): void
    {
        $this->joins[] = $join;
    }

    /**
     * @return Join|null
     */
    public function last(): ?Join
    {
        return end($this->joins);
    }

    /**
     * @return array<Join>
     */
    public function getJoins(): array
    {
        return $this->joins;
    }

    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->joins);
    }

    public function count(): int
    {
        return count($this->joins);
    }
}
