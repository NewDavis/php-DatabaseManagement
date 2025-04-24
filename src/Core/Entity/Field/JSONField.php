<?php

namespace NewDavis\DatabaseManagement\Core\Entity\Field;

use NewDavis\DatabaseManagement\Core\Entity\Flag\Flag;
use NewDavis\DatabaseManagement\Core\Entity\Flag\PrimaryKey;
use NewDavis\DatabaseManagement\Core\Entity\Flag\Required;

class JSONField extends Field
{
    public function __construct(string $internalName, string $storageName, ...$flags)
    {
        parent::__construct($internalName, 'JSON', -1, $storageName, ...$flags);
    }
}