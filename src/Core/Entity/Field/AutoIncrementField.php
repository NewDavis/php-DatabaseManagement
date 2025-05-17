<?php

namespace NewDavis\DatabaseManagement\Core\Entity\Field;

use NewDavis\DatabaseManagement\Core\Entity\Flag\AutoIncrement;
use NewDavis\DatabaseManagement\Core\Entity\Flag\Required;
use NewDavis\DatabaseManagement\Core\Entity\Flag\Unique;

class AutoIncrementField extends Field
{
    public function __construct(string $internalName = 'autoIncrement', string $storageName = 'auto_increment', ...$flags)
    {
        $flags[] = new Unique();
        $flags[] = new Required();
        $flags[] = new AutoIncrement();

        parent::__construct($internalName, $storageName, 'INT', -1, ...$flags);
    }
}