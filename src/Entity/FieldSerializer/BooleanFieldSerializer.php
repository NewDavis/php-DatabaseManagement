<?php

namespace NewDavis\DatabaseManagement\Entity\FieldSerializer;

class BooleanFieldSerializer extends AbstractFieldSerializer
{
    public function encode(mixed $value): int
    {
        if (is_bool($value)) {
            return $value ? (int)1 : (int)0;
        }

        return 0;
    }

    public function decode(mixed $data): bool
    {
        if ($data === 0 || $data === 1) {
            return (bool)$data;
        }

        return false;
    }

    public function validate(mixed $value): bool
    {
        return true;
    }
}