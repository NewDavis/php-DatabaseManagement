<?php

namespace NewDavis\DatabaseManagement\Entity\Field\Scalar;

use NewDavis\DatabaseManagement\Entity\FieldSerializer\AbstractFieldSerializer;

class StringField extends ScalarField
{
    public function __construct(
        string $internalName,
        string $storageName,
        int $length = 255,
        array $flags = []
    ) {
        parent::__construct($internalName, $storageName, 'VARCHAR', $length, $flags);
    }

    public function getSerializer(): ?AbstractFieldSerializer
    {
        return null;
    }
}