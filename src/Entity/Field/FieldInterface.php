<?php

namespace NewDavis\DatabaseManagement\Entity\Field;

interface FieldInterface
{
    /**
     * @return string
     */
    public function getInternalName(): string;
}