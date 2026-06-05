<?php

namespace NewDavis\DatabaseManagement\Entity\Read\Criteria\Filter;

class MultiNotFilter extends MultiFilter
{
    public static function build(string $query, array $parameters): FilterResult
    {
        return new FilterResult(
            sprintf("NOT (%s)", $query),
            $parameters
        );
    }
}