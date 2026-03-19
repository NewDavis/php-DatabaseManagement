<?php

namespace NewDavis\DatabaseManagement\Entity\Read\Criteria\Filter;

class FilterResult
{
    public function __construct(
        private string $query,
        private array $parameters,
    ) {
    }

    /**
     * @return string
     */
    public function getQuery(): string
    {
        return $this->query;
    }

    /**
     * @return array
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }
}