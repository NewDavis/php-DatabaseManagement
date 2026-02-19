<?php

namespace NewDavis\DatabaseManagement\Entity\Field\Scalar;

use NewDavis\DatabaseManagement\Entity\Field\Field;

class DateTimeField extends Field
{
    public const FORMAT = 'Y-m-d H:i:s.u';

    public function __construct(
        string $internalName,
        string $storageName,
        array ...$flags
    ) {
        parent::__construct(
            $internalName,
            $storageName,
            'DATETIME',
            3,
            ...$flags
        );
    }
}