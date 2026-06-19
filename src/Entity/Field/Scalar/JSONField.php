<?php

namespace NewDavis\DatabaseManagement\Entity\Field\Scalar;

use NewDavis\DatabaseManagement\Entity\FieldSerializer\AbstractFieldSerializer;
use NewDavis\DatabaseManagement\Entity\FieldSerializer\DefaultSerializer;
use NewDavis\DatabaseManagement\Entity\FieldSerializer\JSONFieldSerializer;

class JSONField extends ScalarField
{
    public function __construct(
        string $internalName,
        string $storageName,
        array $flags = []
    ) {
        parent::__construct($internalName, $storageName, 'JSON', null, $flags);
    }

    public function getSerializer(): AbstractFieldSerializer
    {
        return new JSONFieldSerializer($this);
    }
}
