<?php

namespace NewDavis\DatabaseManagement\Entity\Field\Scalar;

use NewDavis\DatabaseManagement\Entity\FieldSerializer\AbstractFieldSerializer;
use NewDavis\DatabaseManagement\Entity\FieldSerializer\DateTimeFieldSerializer;
use NewDavis\DatabaseManagement\Entity\FieldSerializer\DefaultSerializer;

class DateTimeField extends ScalarField
{
    public const FORMAT = 'Y-m-d H:i:s.u';

    public function __construct(
        string $internalName,
        string $storageName,
        array $flags = []
    ) {
        parent::__construct(
            $internalName,
            $storageName,
            'DATETIME',
            3,
            $flags
        );
    }

    public function getSerializer(): AbstractFieldSerializer
    {
        return new DateTimeFieldSerializer($this);
    }
}
