<?php

namespace DatabaseManagement\Core\Criteria\Filter;

class EqualsAnyFilter implements Filter
{

    public function __construct(private readonly string $property,
                                private readonly array $values)
    {}

    /**
     * @return string
     */
    public function getProperty(): string
    {
        return $this->property;
    }

    /**
     * @return array
     */
    public function getValues(): array
    {
        return $this->values;
    }

}