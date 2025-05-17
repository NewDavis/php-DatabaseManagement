<?php

namespace NewDavis\DatabaseManagement\Core\Entity\Field;

use NewDavis\DatabaseManagement\Core\Entity\Flag\PrimaryKey;
use NewDavis\DatabaseManagement\Core\Entity\Flag\Required;

class BooleanField extends Field
{
    public function __construct(string $internalName, string $storageName, ...$flags)
    {
        parent::__construct($internalName, $storageName, 'TINYINT', 1, ...$flags);
    }
}