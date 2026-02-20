<?php

namespace NewDavis\DatabaseManagement\Entity\Field\Scalar;

class JSONField extends ScalarField
{
    public function __construct(
        string $internalName,
        string $storageName,
        array $flags = []
    ) {
        parent::__construct($internalName, $storageName, 'JSON', null, $flags);
    }
}