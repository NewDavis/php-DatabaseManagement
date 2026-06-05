<?php

namespace NewDavis\DatabaseManagement\Entity\Field\Scalar;

use NewDavis\DatabaseManagement\Entity\FieldSerializer\AbstractFieldSerializer;
use NewDavis\DatabaseManagement\Entity\FieldSerializer\DefaultSerializer;

class IntegerField extends ScalarField
{
    public function __construct(
        string $internalName,
        string $storageName,
        array $flags = []
    ) {
        parent::__construct($internalName, $storageName, 'INT', null, $flags);
    }

    public function getSerializer(): AbstractFieldSerializer
    {
        return new DefaultSerializer($this);
    }
}
