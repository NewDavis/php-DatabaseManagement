<?php

namespace NewDavis\DatabaseManagement\Entity\FieldSerializer;

use NewDavis\DatabaseManagement\Entity\Field\Field;

class PasswordFieldSerializer extends AbstractFieldSerializer
{
    /**
     * @param string $value
     * @return mixed
     */
    public function encode(mixed $value): mixed
    {
        if (null == $value || !is_string($value)) {
            return null;
        }

        return password_hash($value, PASSWORD_ARGON2ID);
    }

    public function decode(mixed $data): mixed
    {
        return $data;
    }

    public function validate(mixed $value): bool
    {
        return true;
    }
}
