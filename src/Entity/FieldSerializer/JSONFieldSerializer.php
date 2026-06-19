<?php

namespace NewDavis\DatabaseManagement\Entity\FieldSerializer;

class JSONFieldSerializer extends AbstractFieldSerializer
{
    public function encode(mixed $value): string
    {
        if (is_string($value)) {
            return $value;
        }

        return json_encode($value);
    }

    public function decode(mixed $data): mixed
    {
        if (is_array($data)) {
            return $data;
        }

        return json_decode($data, true);
    }

    public function validate(mixed $value): bool
    {
        return true;
    }
}