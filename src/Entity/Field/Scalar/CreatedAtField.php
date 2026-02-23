<?php

namespace NewDavis\DatabaseManagement\Entity\Field\Scalar;

use NewDavis\DatabaseManagement\Entity\Field\Flag\DefaultValue;
use NewDavis\DatabaseManagement\Entity\Field\Flag\Required;

class CreatedAtField extends ScalarField
{
    public function __construct() {
        parent::__construct(
            'createdAt',
            'created_at',
            'DATETIME',
            3,
            [new DefaultValue('CURRENT_TIMESTAMP(3)'), new Required()]
        );
    }
}