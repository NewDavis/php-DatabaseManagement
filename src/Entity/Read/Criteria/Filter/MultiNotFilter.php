<?php

namespace NewDavis\DatabaseManagement\Entity\Read\Criteria\Filter;

class MultiNotFilter extends MultiFilter
{
    public static function build(mixed $value, ?string $property): string
    {
        return "NOT (%s)";
    }
}