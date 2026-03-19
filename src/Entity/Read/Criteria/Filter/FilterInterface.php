<?php

namespace NewDavis\DatabaseManagement\Entity\Read\Criteria\Filter;

interface FilterInterface
{
    public static function build(mixed $value, ?string $property): string;
}