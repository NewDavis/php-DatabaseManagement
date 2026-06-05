<?php

namespace NewDavis\DatabaseManagement\Entity\Field\Scalar;

use NewDavis\DatabaseManagement\Entity\Field\Serializable;

interface ScalarFieldInterface extends Serializable
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