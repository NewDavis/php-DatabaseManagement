<?php

namespace NewDavis\DatabaseManagement\Core\Entity\Property;

use NewDavis\DatabaseManagement\Core\Entity\Property\Flags\Nullable;

class UpdatedAtProperty extends Property
{

    public function __construct()
    {
        parent::__construct('updated_at', 'DATETIME', 3, [new Nullable()]);
    }

}