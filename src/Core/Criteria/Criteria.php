<?php

namespace DatabaseManagement\Core\Criteria;

use DatabaseManagement\Core\Criteria\Filter\Filter;

class Criteria
{

    private int $limit = -1;
    private array $filters = [];
    private array $relations = [];

    public function addFilter(Filter $filter) : static
    {
        $this->filters[] = $filter;

        return $this;
    }

    public function addRelation(string $relation) : static
    {
        $this->relations[] = $relation;

        return $this;
    }

    /**
     * @return array
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    /**
     * @return array
     */
    public function getRelations(): array
    {
        return $this->relations;
    }

    /**
     * @param int $limit
     */
    public function setLimit(int $limit): void
    {
        $this->limit = $limit;
    }

    /**
     * @return int
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

}