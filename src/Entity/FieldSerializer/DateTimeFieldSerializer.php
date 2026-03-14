<?php

namespace NewDavis\DatabaseManagement\Entity\FieldSerializer;

use DateTimeImmutable;
use NewDavis\DatabaseManagement\Entity\Field\Scalar\DateTimeField;
use NewDavis\DatabaseManagement\ORM;

class DateTimeFieldSerializer extends AbstractFieldSerializer
{
    public function encode(mixed $value): mixed
    {
        if (!($value instanceof \DateTimeImmutable)) {
            return ORM::DEFAULT;
        }

        return $value->format(DateTimeField::FORMAT);
    }

    public function decode(mixed $data): mixed
    {
        if ($data instanceof \DateTimeImmutable) {
            return $data;
        }

        return \DateTimeImmutable::createFromFormat(DateTimeField::FORMAT, $data);
    }

    public function validate(mixed $value): bool
    {
        return $value instanceof DateTimeImmutable || is_string($value);
    }
}
