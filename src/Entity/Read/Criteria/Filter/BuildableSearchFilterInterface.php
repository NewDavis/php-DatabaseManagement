<?php

namespace NewDavis\DatabaseManagement\Entity\Read\Criteria\Filter;

interface BuildableSearchFilterInterface
{
    public static function build(mixed $value, ?string $property): FilterResult;
}