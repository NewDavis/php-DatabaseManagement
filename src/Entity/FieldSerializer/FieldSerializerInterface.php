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
    public function encode(mixed $value): mixed;

    /**
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
