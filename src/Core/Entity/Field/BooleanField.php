<?php

namespace NewDavis\DatabaseManagement\Core\Entity\Field;

use NewDavis\DatabaseManagement\Core\Entity\Flag\PrimaryKey;
use NewDavis\DatabaseManagement\Core\Entity\Flag\Required;

class BooleanField extends Field
{
    public function __construct(string $internalName = 'id', string $storageName = 'id', ...$flags)
    {
        parent::__construct($internalName, 'TINYINT', 1, $storageName, ...$flags);
    }
}