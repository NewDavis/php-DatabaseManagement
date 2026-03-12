<?php

namespace NewDavis\DatabaseManagement\Entity\Field\Scalar;

use NewDavis\DatabaseManagement\Entity\FieldSerializer\AbstractFieldSerializer;
use NewDavis\DatabaseManagement\Entity\FieldSerializer\PasswordFieldSerializer;

class PasswordField extends ScalarField
{
    public function __construct(
        string $internalName,
        string $storageName,
        array $flags = []
    ) {
        parent::__construct($internalName, $storageName, 'VARCHAR', 255, $flags);
    }

    public function getSerializer(): AbstractFieldSerializer
    {
        return new PasswordFieldSerializer($this);
    }
}
