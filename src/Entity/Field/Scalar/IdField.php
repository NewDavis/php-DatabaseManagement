<?php

namespace NewDavis\DatabaseManagement\Entity\Field\Scalar;

use NewDavis\DatabaseManagement\Entity\Field\Flag\PrimaryKey;

class IdField extends ScalarField
{
    public function __construct()
    {
        parent::__construct(
            'id',
            'id',
            'UUID',
            null,
            [new PrimaryKey()]
        );
    }
}