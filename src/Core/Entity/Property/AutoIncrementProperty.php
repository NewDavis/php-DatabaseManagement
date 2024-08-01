<?php

namespace DatabaseManagement\Core\Entity\Property;

use DatabaseManagement\Core\Entity\Property\Flags\AutoIncrement;
use DatabaseManagement\Core\Entity\Property\Flags\PrimaryKey;

class AutoIncrementProperty extends Property
{

    public function __construct()
    {
        parent::__construct('auto_increment', 'INT', -1, [new PrimaryKey(), new AutoIncrement()]);
    }

}