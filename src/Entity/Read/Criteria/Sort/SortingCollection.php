<?php

namespace NewDavis\DatabaseManagement\Entity\Read\Criteria\Sort;

use Traversable;

class SortingCollection implements \IteratorAggregate, \Countable
{
    public function __construct(
        private array $sortings = []
    ) {
    }

    public function add(SortingInterface $sorting): void
    {
        $this->sortings[] = $sorting;
    }

    public function remove(SortingInterface $sorting): void
    {
        $index = array_search($sorting, $this->sortings, true);

        if ($index === false) return;

        unset($this->sortings[$index]);
    }

    /**
     * @return array<SortingInterface>
     */
    public function getSortings(): array
    {
        return $this->sortings;
    }

    public function getIterator(): Traversable
    {
        return new \ArrayIterator($this->sortings);
    }

    public function count(): int
    {
        return count($this->sortings);
    }
}