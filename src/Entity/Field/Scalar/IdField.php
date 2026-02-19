<?php

namespace NewDavis\DatabaseManagement\Entity\Field\Scalar;

use NewDavis\DatabaseManagement\Entity\Field\Field;

class IdField extends Field
{
    public function __construct()
    {
        parent::__construct(
            'id',
            'id',
            'UUID',
            null,
            // TODO add flags
        );
    }
}