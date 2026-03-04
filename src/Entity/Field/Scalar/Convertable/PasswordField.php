<?php

namespace NewDavis\DatabaseManagement\Entity\Field\Scalar\Convertable;

use NewDavis\DatabaseManagement\Entity\Field\ConvertableFieldInterface;
use NewDavis\DatabaseManagement\Entity\Field\Scalar\ScalarField;
use NewDavis\DatabaseManagement\Entity\FieldSerializer\AbstractFieldSerializer;

/**
 * @implements ConvertableFieldInterface<string>
 */
class PasswordField extends ScalarField implements ConvertableFieldInterface
{
    public function __construct(
        string $internalName,
        string $storageName,
        array $flags = []
    ) {
        parent::__construct($internalName, $storageName, 'VARCHAR', 255, $flags);
    }

    public function convert(mixed $input): mixed
    {
        if (null == $input || !is_string($input)) {
            return null;
        }

        return password_hash($input, PASSWORD_ARGON2ID);
    }

    public function getSerializer(): ?AbstractFieldSerializer
    {
        return null;
    }
}