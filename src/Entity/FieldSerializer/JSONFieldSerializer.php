<?php

namespace NewDavis\DatabaseManagement\Entity\FieldSerializer;

class JSONFieldSerializer extends AbstractFieldSerializer
{
    public function encode(mixed $value): string
    {
        return json_encode($value);
    }

    public function decode(mixed $data): mixed
    {
        return json_decode($data, true);
    }

    public function validate(mixed $value): bool
    {
        return true;
    }
}