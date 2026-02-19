<?php

namespace NewDavis\DatabaseManagement\Entity\Field;

use NewDavis\DatabaseManagement\Entity\Field\Flag\Flag;

interface FieldInterface
{
    /**
     * @return string
     */
    public function getInternalName(): string;

    /**
     * @return array<Flag>
     */
    public function getFlags(): array;
}