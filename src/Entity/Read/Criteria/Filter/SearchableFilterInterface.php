<?php

namespace NewDavis\DatabaseManagement\Entity\Read\Criteria\Filter;

interface SearchableFilterInterface extends FilterInterface
{
    public function getInternalName(): string;
    public function getSearchValue(): mixed;
}