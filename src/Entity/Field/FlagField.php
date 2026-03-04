<?php

namespace NewDavis\DatabaseManagement\Entity\Field;

use NewDavis\DatabaseManagement\Entity\Field\Flag\Flag;

class FlagField extends Field implements SupportsFlags
{
    /**
     * @param array<Flag> $flags
     */
    public function __construct(private readonly array $flags)
    {
        parent::__construct('flag');
    }

    public function getFlags(): array
    {
        return $this->flags;
    }
}