<?php

namespace NewDavis\DatabaseManagement\Entity;

abstract class AbstractStatement
{
    public function __construct(
        private readonly string $query,
        private readonly array $parameters
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