<?php

namespace NewDavis\DatabaseManagement\Entity\Field\Scalar;

use NewDavis\DatabaseManagement\Entity\Field\Flag\PrimaryKey;
use NewDavis\DatabaseManagement\Entity\Field\Flag\Required;
use NewDavis\DatabaseManagement\Entity\FieldSerializer\AbstractFieldSerializer;
use NewDavis\DatabaseManagement\Entity\FieldSerializer\DefaultSerializer;
use NewDavis\DatabaseManagement\Entity\FieldSerializer\IdFieldSerializer;

class IdField extends ScalarField
{
    public function __construct(string $internalName = 'id', string $storageName = 'id')
    {
        parent::__construct(
            $internalName,
            $storageName,
            'BINARY',
            16,
            [new PrimaryKey(), new Required()]
        );
    }

    public function getSerializer(): AbstractFieldSerializer
    {
        return new IdFieldSerializer($this);
    }
}
