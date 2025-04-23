<?php

namespace NewDavis\DatabaseManagement\Core\Entity\Field;

use NewDavis\DatabaseManagement\Core\Entity\Flag\PrimaryKey;
use NewDavis\DatabaseManagement\Core\Entity\Flag\Required;

class IdField extends Field
{
    public function __construct(string $internalName = 'id', string $storageName = 'id', ...$flags)
    {
        $flags[] = new PrimaryKey();
        $flags[] = new Required();

        parent::__construct($internalName, 'UUID', -1, $storageName, ...$flags);
    }
}