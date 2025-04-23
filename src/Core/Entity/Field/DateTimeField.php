<?php

namespace NewDavis\DatabaseManagement\Core\Entity\Field;

class DateTimeField extends Field
{
    public function __construct(string $internalName, string $storageName, ...$flags)
    {
        parent::__construct($internalName, 'DATETIME', 3, $storageName, ...$flags);
    }
}