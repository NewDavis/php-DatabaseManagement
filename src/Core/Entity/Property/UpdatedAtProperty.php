<?php

namespace DatabaseManagement\Core\Entity\Property;

use DatabaseManagement\Core\Entity\Property\Flags\Nullable;

class UpdatedAtProperty extends Property
{

    public function __construct()
    {
        //parent::__construct('updated_at', 'DATETIME', 3, [new Nullable()]);
        parent::__construct('updated_at', 'VARCHAR', 25, [new Nullable()]);
    }

}