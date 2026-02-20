<?php

namespace NewDavis\DatabaseManagement\Entity\Field;

/** @template T */
interface ConvertableFieldInterface
{
    /**
     * @param T $input
     * @return T
     */
    public function convert(mixed $input): mixed;
}