<?php

namespace NewDavis\DatabaseManagement\Core\Entity\Property;

class CreatedAtProperty extends Property
{

    public function __construct()
    {
        parent::__construct('created_at', 'DATETIME', 3);
    }

}