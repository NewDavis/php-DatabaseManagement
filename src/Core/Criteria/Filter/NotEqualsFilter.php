<?php

namespace NewDavis\DatabaseManagement\Core\Criteria\Filter;

class NotEqualsFilter implements \NewDavis\DatabaseManagement\Core\Criteria\Filter\Filter
{

    public function __construct(private readonly string $property,
                                private readonly mixed $value)
    {}

    /**
     * @return string
     */
    public function getProperty(): string
    {
        return $this->property;
    }

    /**
     * @return mixed
     */
    public function getValue(): mixed
    {
        return $this->value;
    }

}