<?php

namespace NewDavis\DatabaseManagement\Core\Search\Criteria;

use NewDavis\DatabaseManagement\Core\Schema\SchemaBuilder;
use NewDavis\DatabaseManagement\Core\Search\Criteria\Filter\Exception\UnknownInternalNameException;
use NewDavis\DatabaseManagement\Core\Search\Criteria\Filter\Filter;
use NewDavis\DatabaseManagement\Core\Search\Criteria\Sorting\Sorting;

class Criteria
{
    /** @var int */
    private int $limit = -1;
    /** @var int */
    private int $offset = -1;
    /** @var int */
    private int $page = 1;
    /** @var array<Filter> */
    private array $filter = [];
    /** @var string<Sorting> */
    private array $sorting = [];
    /** @var array $associations */
    private array $associations = [];

    /**
     * @param int $limit
     * @return $this
     */
    public function setLimit(int $limit): Criteria
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * @return int
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * @param int $offset
     */
    public function setOffset(int $offset): void
    {
        $this->offset = $offset;
    }

    /**
     * @return int
     */
    public function getOffset(): int
    {
        return $this->offset;
    }

    /**
     * @param int $page
     */
    public function setPage(int $page): void
    {
        $this->page = $page;
    }

    /**
     * @return int
     */
    public function getPage(): int
    {
        return $this->page;
    }

    /**
     * @param Filter $filter
     * @return $this
     */
    public function addFilter(Filter $filter): Criteria
    {
        $this->filter[] = $filter;

        return $this;
    }

    /**
     * @return array<Filter>
     */
    public function getFilter(): array
    {
        return $this->filter;
    }

    /**
     * @param Sorting $sorting
     * @return $this
     */
    public function addSorting(Sorting $sorting): Criteria
    {
        $this->sorting[] = $sorting;

        return $this;
    }

    /**
     * @return array
     */
    public function getSorting(): array
    {
        return $this->sorting;
    }

    /**
     * @return array
     */
    public function getAssociations(): array
    {
        return $this->associations;
    }

    /**
     * @param string[] $associations
     * @return void
     */
    public function addAssociations(string... $associations): Criteria
    {
        foreach ($associations as $association) {
            $this->addAssociation($association);
        }

        return $this;
    }

    /**
     * @param string $association
     * @return void
     */
    public function addAssociation(string $association): Criteria
    {
        if(in_array($association, $this->associations)) return $this;

        $this->associations[] = $association;

        return $this;
    }

}