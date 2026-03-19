<?php

namespace NewDavis\DatabaseManagement\Entity\Read\Criteria\Filter;

class MultiFilter implements MultiFilterInterface
{
    private FilterCollection $filters;

    public function __construct(
        array $filters,
        private readonly MultiFilterType $type = MultiFilterType::AND
    ) {
        $this->filters = new FilterCollection($filters);
    }

    /**
     * @return FilterCollection
     */
    public function getFilters(): FilterCollection
    {
        return $this->filters;
    }

    public function getType(): MultiFilterType
    {
        return $this->type;
    }

    public static function build(mixed $value, ?string $property): string
    {
        return "(%s)";
    }
}