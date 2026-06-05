<?php

namespace NewDavis\DatabaseManagement\Entity\Read\Criteria\Filter;

use Traversable;

class FilterCollection implements \IteratorAggregate, \Countable
{
    public function __construct(
        private array $filters = []
    ) {
    }

    public function add(FilterInterface $filter): void
    {
        $this->filters[] = $filter;
    }

    public function remove(FilterInterface $filter): void
    {
        $index = array_search($filter, $this->filters, true);

        if ($index === false) return;

        unset($this->filters[$index]);
    }

    public function filter(string $className): array
    {
        return array_values(
            array_filter(
                $this->filters,
                fn(FilterInterface $filter) => $filter instanceof $className,
            )
        );
    }

    /**
     * @return array<FilterInterface>
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    public function getIterator(): Traversable
    {
        return new \ArrayIterator($this->filters);
    }

    public function count(): int
    {
        return count($this->filters);
    }
}