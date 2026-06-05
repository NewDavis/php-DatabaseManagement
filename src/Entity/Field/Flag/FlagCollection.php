<?php

namespace NewDavis\DatabaseManagement\Entity\Field\Flag;

use Traversable;

class FlagCollection implements \IteratorAggregate, \Countable
{
    public function __construct(
        private array $flags,
    ) {
    }

    public function add(Flag $flag): void
    {
        $this->flags[] = $flag;
    }

    public function filter(string $className): array
    {
        return array_values(
            array_filter(
                $this->flags,
                fn(Flag $flag) => $flag instanceof $className,
            )
        );
    }

    public function filterByType(FlagType $type): array
    {
        return array_filter(
            $this->flags,
            fn (Flag $flag) => $flag->getTypes()->hasType($type)
        );
    }

    /** @return array<Flag> */
    public function getFlags(): array
    {
        return $this->flags;
    }

    public function getIterator(): Traversable
    {
        return new \ArrayIterator($this->flags);
    }

    public function count(): int
    {
        return count($this->flags);
    }
}
