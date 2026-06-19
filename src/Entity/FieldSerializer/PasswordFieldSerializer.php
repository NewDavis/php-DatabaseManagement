<?php

namespace NewDavis\DatabaseManagement\Entity\FieldSerializer;

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

        if (password_get_info($value)['algo'] !== null) {
            return $value;
        }

        return password_hash($value, PASSWORD_ARGON2ID);
    }

    public function decode(mixed $data): mixed
    {
        return $this->encode($data);
    }

    public function validate(mixed $value): bool
    {
        return true;
    }
}
