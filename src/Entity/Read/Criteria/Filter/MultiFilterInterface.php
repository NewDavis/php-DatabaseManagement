<?php

namespace NewDavis\DatabaseManagement\Entity\Read\Criteria\Filter;

interface MultiFilterInterface extends FilterInterface
{
    public function getFilters(): FilterCollection;
    public function getType(): MultiFilterType;
    public static function build(string $query, array $parameters): FilterResult;
}