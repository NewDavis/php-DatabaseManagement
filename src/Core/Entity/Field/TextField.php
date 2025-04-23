<?php

namespace NewDavis\DatabaseManagement\Core\Entity\Field;

use NewDavis\DatabaseManagement\Core\Entity\Flag\Flag;
use NewDavis\DatabaseManagement\Core\Entity\Flag\PrimaryKey;
use NewDavis\DatabaseManagement\Core\Entity\Flag\Required;

class TextField extends Field
{
    public function __construct(string $internalName, int $length, string $storageName, ...$flags)
    {
        parent::__construct($internalName, 'VARCHAR', $length, $storageName, ...$flags);
    }
}