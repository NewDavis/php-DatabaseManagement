<?php

namespace NewDavis\DatabaseManagement\Entity\FieldSerializer\Relational;

use NewDavis\DatabaseManagement\Entity\EntityCollectionInterface;

class EntityCollectionSerializer extends AbstractRelationalFieldSerializer
{
    public function encode(mixed $value): EntityCollectionInterface
    {
        return $value;
    }

    public function decode(mixed $data): EntityCollectionInterface
    {
        return $this->encode($data);
    }

    public function validate(mixed $value): bool
    {
        return $value instanceof EntityCollectionInterface;
    }
}