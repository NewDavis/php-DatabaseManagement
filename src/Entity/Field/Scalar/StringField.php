<?php

namespace NewDavis\DatabaseManagement\Entity\Field\Scalar;

use NewDavis\DatabaseManagement\Entity\Field\Field;

class StringField extends Field
{
    public function __construct(
        string $internalName,
        string $storageName,
        int $length = 255,
        array ...$flags
    ) {
        parent::__construct($internalName, $storageName, 'VARCHAR', $length, ...$flags);
    }
}