<?php

namespace NewDavis\DatabaseManagement\Core\Entity\Field;

class DateTimeField extends Field
{
    const FORMAT = 'Y-m-d H:i:s.u';

    public function __construct(string $internalName, string $storageName, ...$flags)
    {
        parent::__construct($internalName, $storageName, 'DATETIME', 3, ...$flags);
    }
}