<?php

namespace NewDavis\DatabaseManagement\Entity\Read\Criteria\Sort;

class FieldSorting implements SortingInterface
{
    public function __construct(
        private readonly string $property,
        private readonly FieldSortingDirection $direction
    ) {
    }

    /**
     * @return string
     */
    public function getProperty(): string
    {
        return $this->property;
    }

    /**
     * @return FieldSortingDirection
     */
    public function getDirection(): FieldSortingDirection
    {
        return $this->direction;
    }
}