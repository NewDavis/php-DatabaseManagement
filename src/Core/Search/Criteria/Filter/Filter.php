<?php

namespace NewDavis\DatabaseManagement\Core\Search\Criteria\Filter;

interface Filter
{
    public function convert(string $definition): string;
}