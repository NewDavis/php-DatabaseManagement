<?php

namespace NewDavis\DatabaseManagement\Entity\Read\Criteria\Filter;

class EqualsFilter implements FilterInterface
{
    public function __construct(
        private readonly string $internalName,
        private readonly mixed $searchValue,
    ) {
    }

    public function getInternalName(): string
    {
        return $this->internalName;
    }

    public function getSearchValue(): mixed
    {
        return $this->searchValue;
    }
}