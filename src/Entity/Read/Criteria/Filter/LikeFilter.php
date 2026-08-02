<?php

namespace NewDavis\DatabaseManagement\Entity\Read\Criteria\Filter;

class LikeFilter implements SearchableFilterInterface
{
    public function __construct(
        private readonly string $internalName,
        private readonly mixed $searchValue,
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
                "%s LIKE ?",
                $property
            ),
            [$value]
        );
    }
}