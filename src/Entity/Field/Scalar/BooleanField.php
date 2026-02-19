<?php

namespace NewDavis\DatabaseManagement\Entity\Field\Scalar;

use NewDavis\DatabaseManagement\Entity\Field\Field;

class BooleanField extends Field
{
    public function __construct(
        string $internalName,
        string $storageName,
        array ...$flags
    ) {
        parent::__construct($internalName, $storageName, 'TINYINT', 1, ...$flags);
    }
}