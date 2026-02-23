<?php

namespace NewDavis\DatabaseManagement\Entity\Field\Scalar;

use NewDavis\DatabaseManagement\Entity\Field\Flag\ConstraintActions;
use NewDavis\DatabaseManagement\Entity\Field\Flag\Nullable;
use NewDavis\DatabaseManagement\Entity\Field\Flag\OnUpdate;

class UpdatedAtField extends ScalarField
{
    public function __construct()
    {
        parent::__construct(
            'updatedAt',
            'updated_at',
            'DATETIME',
            3,
            [new OnUpdate(ConstraintActions::CUSTOM, 'CURRENT_TIMESTAMP(3)'), new Nullable()]
        );
    }
}