<?php

namespace NewDavis\DatabaseManagement\Entity\Field\Scalar;

use NewDavis\DatabaseManagement\Entity\Field\Flag\PrimaryKey;
use NewDavis\DatabaseManagement\Entity\Field\Flag\Required;

class IdField extends ScalarField
{
    public function __construct()
    {
        parent::__construct(
            'id',
            'id',
            'BINARY',
            16,
            [new PrimaryKey(), new Required()]
        );
    }
}