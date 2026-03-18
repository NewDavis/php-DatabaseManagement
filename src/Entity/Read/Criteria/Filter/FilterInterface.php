<?php

namespace NewDavis\DatabaseManagement\Entity\Read\Criteria\Filter;

interface FilterInterface
{
    public function getInternalName(): string;
    public function getSearchValue(): mixed;
}