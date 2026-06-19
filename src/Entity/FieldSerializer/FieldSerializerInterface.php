<?php

namespace NewDavis\DatabaseManagement\Entity\FieldSerializer;

/** @template T */
interface FieldSerializerInterface
{
    /**
     * Return value will be used in database
     * @param T $value
     * @return mixed
     */
    public function encode(mixed $value): mixed;

    /**
     * Return value will be set in the entity
     * @param mixed $data
     * @return T
     */
    public function decode(mixed $data): mixed;

    /**
     * @param T $value
     * @return bool
     */
    public function validate(mixed $value): bool;
}
