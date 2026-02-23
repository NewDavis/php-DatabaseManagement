<?php

namespace NewDavis\DatabaseManagement\Entity\Field\Flag;

class FlagTypeCollection
{
    public function __construct(
        private readonly array $flagTypes
    ) {
    }

    public function hasType(FlagType $type): bool
    {
        return in_array($type, $this->flagTypes);
    }
}