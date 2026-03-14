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
        return $value;
    }

    public function decode(mixed $data): mixed
    {
        if (null == $data || !is_string($data)) {
            return null;
        }

        if (str_starts_with($data, '$argon2id$')) {
            return $data;
        }

        return password_hash($data, PASSWORD_ARGON2ID);
    }

    public function validate(mixed $value): bool
    {
        return true;
    }
}
