<?php

namespace NewDavis\DatabaseManagement\Entity\Field\Scalar;

class IntegerField extends ScalarField
{
    public function __construct(
        string $internalName,
        string $storageName,
        array $flags = []
    ) {
        parent::__construct($internalName, $storageName, 'INT', null, $flags);
    }
}