<?php

namespace NewDavis\DatabaseManagement\Entity\FieldSerializer;

class BooleanFieldSerializer extends AbstractFieldSerializer
{
    public function encode(mixed $value): int
    {
        if (is_bool($value)) {
            return $value ? 1 : 0;
        } else if (is_numeric($value)) {
            return $value;
        }

        return 0;
    }

    public function decode(mixed $data): bool
    {
        if (is_bool($data)) {
            return $data;
        } else if (is_numeric($data)) {
            return (bool)$data;
        }

        return false;
    }

    public function validate(mixed $value): bool
    {
        return true;
    }
}