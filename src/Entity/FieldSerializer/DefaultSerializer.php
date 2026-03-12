<?php

namespace NewDavis\DatabaseManagement\Entity\FieldSerializer;

class DefaultSerializer extends AbstractFieldSerializer
{
    public function encode(mixed $value): mixed
    {
        return $value;
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
