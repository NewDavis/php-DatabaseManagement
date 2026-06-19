<?php

namespace NewDavis\DatabaseManagement\Entity\Read\Criteria;

use NewDavis\DatabaseManagement\Entity\Read\Criteria\Filter\EqualsAnyFilter;
use NewDavis\DatabaseManagement\Entity\Read\Criteria\Filter\EqualsFilter;
use NewDavis\DatabaseManagement\Entity\Read\Criteria\Filter\FilterCollection;
use NewDavis\DatabaseManagement\Entity\Read\Criteria\Filter\FilterInterface;
use NewDavis\DatabaseManagement\Entity\Read\Criteria\Sort\SortingCollection;
use NewDavis\DatabaseManagement\Entity\Read\Criteria\Sort\SortingInterface;

class Criteria
{
    const NO_LIMIT = -1;
    const NO_OFFSET = -1;

    private ?string $title = null;
    private int $limit;
    private int $offset;
    private int $page;
    private readonly FilterCollection $filters;
    private readonly SortingCollection $sortingCollection;
    private array $associations;

    public function __construct(
        private readonly array $ids = [],
        int $limit = self::NO_LIMIT,
        int $page = 1,
    ) {
        $this->filters = new FilterCollection();
        $this->sortingCollection = new SortingCollection();
        $this->associations = [];

        $this->limit = $limit;
        $this->page = min($page, 1);
        $this->offset = $this->page * $this->limit; // calculated by page * limit;

        if (count($this->ids) > 0) {
            $this->filters->add(
                new EqualsAnyFilter('id', $ids)
            );
        }
    }

    /**
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param string|null $title
     */
    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    /**
     * @return int
     */
    public function getLimit(): int
    {
        return $this->limit;
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
    public function getOffset(): int
    {
        return $this->offset;
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
    public function getPage(): int
    {
        return $this->page;
    }

    /**
     * @param int $page
     */
    public function setPage(int $page): void
    {
        $this->page = $page;
    }

    public function addFilter(FilterInterface $filter): Criteria
    {
        $this->filters->add($filter);

        return $this;
    }

    public function removeFilter(FilterInterface $filter): Criteria
    {
        $this->filters->remove($filter);

        return $this;
    }

    public function addSorting(SortingInterface $sorting): Criteria
    {
        $this->sortingCollection->add($sorting);

        return $this;
    }

    public function removeSorting(SortingInterface $sorting): Criteria
    {
        $this->sortingCollection->remove($sorting);

        return $this;
    }

    public function addAssociations(array $associations): Criteria
    {
        foreach ($associations as $association) {
            $this->addAssociation($association);
        }

        return $this;
    }

    public function addAssociation(string $association): Criteria
    {
        if (in_array($association, $this->associations)) return $this;

        $this->associations[] = $association;

        return $this;
    }

    public function removeAssociation(string $association): Criteria
    {
        $index = array_search($association, $this->associations);

        if ($index === false) return $this;

        unset($this->associations[$index]);

        return $this;
    }

    /**
     * @return FilterCollection
     */
    public function getFilters(): FilterCollection
    {
        return $this->filters;
    }

    /**
     * @return SortingCollection
     */
    public function getSortingCollection(): SortingCollection
    {
        return $this->sortingCollection;
    }

    /**
     * @return array
     */
    public function getAssociations(): array
    {
        return $this->associations;
    }
}