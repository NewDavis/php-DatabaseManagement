<?php

namespace NewDavis\DatabaseManagement\Entity\Field\Scalar;

interface ScalarFieldInterface
{
    /**
     * @return string
     */
    public function getType(): string;

    /**
     * @return int|null
     */
    public function getLength(): ?int;
}