<?php

namespace NewDavis\DatabaseManagement\Entity\FieldSerializer;

use NewDavis\DatabaseManagement\ORM;

class AutoIncrementFieldSerializer extends AbstractFieldSerializer
{
    public function encode(mixed $value): mixed
    {
        if (!is_numeric($value)) {
            return ORM::DEFAULT;
        }

        return $value;
    }

    public function decode(mixed $data): mixed
    {
        if (is_numeric($data)) {
            return $data;
        }

        return -1;
    }

    public function validate(mixed $value): bool
    {
        return $value == null || $value === ORM::DEFAULT;
    }
}
