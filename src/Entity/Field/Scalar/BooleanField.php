<?php

namespace NewDavis\DatabaseManagement\Entity\Field\Scalar;

class BooleanField extends ScalarField
{
    public function __construct(
        string $internalName,
        string $storageName,
        array $flags = []
    ) {
        parent::__construct($internalName, $storageName, 'TINYINT', 1, $flags);
    }
}