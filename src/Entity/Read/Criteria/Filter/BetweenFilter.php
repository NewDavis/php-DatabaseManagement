<?php

namespace NewDavis\DatabaseManagement\Entity\Read\Criteria\Filter;

class BetweenFilter implements FilterInterface, BuildableSearchFilterInterface
{
    /**
     * @param string $internalName
     * @param int $start
     * @param int $end
     */
    public function __construct(
        private readonly string $internalName,
        private readonly int $start,
        private readonly int $end,
    ) {
    }

    /**
     * @return string
     */
    public function getInternalName(): string
    {
        return $this->internalName;
    }

    /**
     * @return int
     */
    public function getStart(): int
    {
        return $this->start;
    }

    /**
     * @return int
     */
    public function getEnd(): int
    {
        return $this->end;
    }

    public static function build(mixed $value, ?string $property): FilterResult
    {
        return new FilterResult(
            sprintf(
                "%s BETWEEN ? AND ?",
                $property
            ),
            // TODO
            [0, 0]
        );
    }
}