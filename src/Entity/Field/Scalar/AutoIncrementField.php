<?php

namespace NewDavis\DatabaseManagement\Entity\Field\Scalar;

use NewDavis\DatabaseManagement\Entity\Field\Flag\AutoIncrement;

class AutoIncrementField extends ScalarField
{
    public function __construct()
    {
        parent::__construct(
            'autoIncrement',
            'auto_increment',
            'INT',
            null,
            [new AutoIncrement()]
        );
    }
}