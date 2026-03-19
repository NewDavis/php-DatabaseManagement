<?php

namespace NewDavis\DatabaseManagement\Entity\Field;

use NewDavis\DatabaseManagement\Entity\Field\Flag\Flag;
use NewDavis\DatabaseManagement\Entity\Field\Flag\FlagCollection;

interface SupportsFlags
{
    /**
     * @return FlagCollection
     */
    public function getFlags(): FlagCollection;
}