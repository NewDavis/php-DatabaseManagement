<?php

namespace NewDavis\DatabaseManagement\Entity\Read\Criteria\Filter;

interface SearchableFilterInterface extends FilterInterface, BuildableSearchFilterInterface
{
    public function getInternalName(): string;
    public function getSearchValue(): mixed;
}