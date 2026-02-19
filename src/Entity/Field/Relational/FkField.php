<?php

namespace NewDavis\DatabaseManagement\Entity\Field\Relational;

use NewDavis\DatabaseManagement\Entity\Field\Field;

class FkField extends Field
{
    public function __construct(
        string $internalName,
        string $storageName,
        string $type,
        ?int $length,
        array ...$flags
    ) {
        parent::__construct($internalName, $storageName, $type, $length, ...$flags);
    }
}