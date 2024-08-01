<?php

namespace NewDavis\DatabaseManagement\Core\Entity\Property;

use NewDavis\DatabaseManagement\Core\Entity\Property\Flags\PrimaryKey;
use NewDavis\DatabaseManagement\Core\Entity\Property\Flags\Required;
use NewDavis\DatabaseManagement\Core\Entity\Property\Flags\Unique;

class IdProperty extends Property
{

    public function __construct(string $property = 'id', array $flags = [new Unique(), new PrimaryKey(), new Required()])
    {
        parent::__construct($property, 'VARCHAR', 36, $flags);
    }

}