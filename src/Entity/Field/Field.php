<?php

namespace NewDavis\DatabaseManagement\Entity\Field;

use NewDavis\DatabaseManagement\Entity\Field\Flag\Flag;
use NewDavis\DatabaseManagement\Entity\Field\Scalar\ScalarFieldInterface;

class Field implements FieldInterface
{
    /**
     * @param string $internalName
     */
    public function __construct(
        private readonly string $internalName,
    ) {
    }

    /**
     * @return string
     */
    public function getInternalName(): string
    {
        return $this->internalName;
    }
}