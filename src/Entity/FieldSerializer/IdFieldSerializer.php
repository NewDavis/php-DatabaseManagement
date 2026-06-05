<?php

namespace NewDavis\DatabaseManagement\Entity\FieldSerializer;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class IdFieldSerializer extends AbstractFieldSerializer
{
    public function encode(mixed $value): mixed
    {
        if (is_array($value)) {
            return array_map(
                fn(string $uuid) => Uuid::fromString($uuid)->getBytes(),
                $value
            );
        }

        if (!($value instanceof UuidInterface)) {
            return $value;
        }

        return $value->getBytes();
    }

    public function decode(mixed $data): mixed
    {
        if ($data == null) return $data;

        if ($data instanceof UuidInterface) return $data;

        return Uuid::fromBytes($data);
    }

    public function validate(mixed $value): bool
    {
        return $value instanceof UuidInterface || Uuid::isValid($value);
    }
}
