<?php

namespace NewDavis\DatabaseManagement\Core\Entity\Field;

use NewDavis\DatabaseManagement\Core\Entity\Flag\PrimaryKey;
use NewDavis\DatabaseManagement\Core\Entity\Flag\Required;
use NewDavis\DatabaseManagement\Core\Entity\Flag\Unique;

class IdField extends Field
{
    public function __construct(string $internalName = 'id', string $storageName = 'id', ...$flags)
    {
        $flags[] = new PrimaryKey();
        $flags[] = new Required();

        parent::__construct($internalName, $storageName, 'UUID', -1, ...$flags);
    }
}