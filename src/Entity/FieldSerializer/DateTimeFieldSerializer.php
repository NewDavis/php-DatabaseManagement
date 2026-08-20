<?php

namespace NewDavis\DatabaseManagement\Entity\FieldSerializer;

use DateTimeImmutable;
use NewDavis\DatabaseManagement\Entity\Field\Scalar\DateTimeField;
use NewDavis\DatabaseManagement\ORM;

class DateTimeFieldSerializer extends AbstractFieldSerializer
{
    public function encode(mixed $value): ORM|string
    {
        if (is_string($value)) return $value;

        if (!($value instanceof \DateTimeImmutable)) {
            return ORM::DEFAULT;
        }

        return $value->format(DateTimeField::FORMAT);
    }

    public function decode(mixed $data): DateTimeImmutable|null
    {
        if ($data instanceof \DateTimeImmutable) {
            return $data;
        }

        if (!is_string($data)) {
            return null;
        }

        $decoded = \DateTimeImmutable::createFromFormat(DateTimeField::FORMAT, $data);

        if ($decoded === false) return null;

        return $decoded;
    }

    public function validate(mixed $value): bool
    {
        return $value instanceof DateTimeImmutable || is_string($value);
    }
}
