<?php

namespace NewDavis\DatabaseManagement\Entity\Field;

use NewDavis\DatabaseManagement\Entity\Field\Flag\Flag;
use NewDavis\DatabaseManagement\Entity\Field\Flag\FlagCollection;

class FlagField extends Field implements SupportsFlags
{
    private FlagCollection $flags;

    /**
     * @param array<Flag> $flags
     */
    public function __construct(array $flags)
    {
        parent::__construct('flag');

        $this->flags = new FlagCollection($flags);
    }

    public function getFlags(): FlagCollection
    {
        return $this->flags;
    }
}