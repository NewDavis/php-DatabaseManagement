<?php

namespace NewDavis\DatabaseManagement\Entity\Field;

use NewDavis\DatabaseManagement\Entity\Field\Flag\Flag;

interface SupportsFlags
{
    /**
     * @return Flag[]
     */
    public function getFlags(): array;
}