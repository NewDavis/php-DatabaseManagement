<?php

namespace NewDavis\DatabaseManagement\Entity\Field\Scalar;

use NewDavis\DatabaseManagement\Entity\Field\Field;

class AutoIncrementField extends Field
{
    public function __construct()
    {
        parent::__construct(
            'autoIncrement',
            'auto_increment',
            'INT',
            null,
            // TODO add auto increment flag.
        );
    }
}