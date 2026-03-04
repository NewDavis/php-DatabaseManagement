<?php

namespace NewDavis\DatabaseManagement\Entity\FieldSerializer;

use NewDavis\DatabaseManagement\Entity\Field\Field;

/** @template T */
interface FieldSerializerInterface
{
    /**
     * @param T $value
     * @return mixed
     */
    public function encode(Field $field, mixed $value): mixed;

    /**
     * @param mixed $data
     * @return T
     */
    public function decode(Field $field, mixed $data);

    /**
     * @param T $value
     * @return bool
     */
    public function validate(Field $field, mixed $value): bool;
}