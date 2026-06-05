<?php

namespace NewDavis\DatabaseManagement\Entity\Read\Criteria\Filter;

class EqualsAnyFilter implements SearchableFilterInterface
{
    public function __construct(
        private readonly string $internalName,
        private readonly array $searchValue,
    ) {
    }

    public function getInternalName(): string
    {
        return $this->internalName;
    }

    public function getSearchValue(): mixed
    {
        return $this->searchValue;
    }

    public static function build(mixed $value, ?string $property): FilterResult
    {
        return new FilterResult(
            sprintf(
                "%s IN (%s)",
                $property,
                implode(', ', array_map(
                    fn(string $v) => "?",
                    $value
                ))
            ),
            $value
        );
    }
}