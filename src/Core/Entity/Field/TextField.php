<?php

namespace NewDavis\DatabaseManagement\Core\Entity\Field;

use NewDavis\DatabaseManagement\Core\Entity\Flag\Flag;
use NewDavis\DatabaseManagement\Core\Entity\Flag\PrimaryKey;
use NewDavis\DatabaseManagement\Core\Entity\Flag\Required;

class TextField extends Field
{
    public function __construct(string $internalName, string $storageName, int $length, ...$flags)
    {
        parent::__construct($internalName, $storageName, 'VARCHAR', $length, ...$flags);
    }
}