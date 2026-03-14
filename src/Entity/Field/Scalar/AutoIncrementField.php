<?php

namespace NewDavis\DatabaseManagement\Entity\Field\Scalar;

use NewDavis\DatabaseManagement\Entity\Field\Flag\AutoIncrement;
use NewDavis\DatabaseManagement\Entity\Field\Flag\Required;
use NewDavis\DatabaseManagement\Entity\Field\Flag\Unique;
use NewDavis\DatabaseManagement\Entity\FieldSerializer\AbstractFieldSerializer;
use NewDavis\DatabaseManagement\Entity\FieldSerializer\AutoIncrementFieldSerializer;
use NewDavis\DatabaseManagement\Entity\FieldSerializer\DefaultSerializer;

class AutoIncrementField extends ScalarField
{
    public function __construct()
    {
        parent::__construct(
            'autoIncrement',
            'auto_increment',
            'INT',
            null,
            [new Required(), new Unique(), new AutoIncrement()]
        );
    }

    public function getSerializer(): AbstractFieldSerializer
    {
        return new AutoIncrementFieldSerializer($this);
    }
}
