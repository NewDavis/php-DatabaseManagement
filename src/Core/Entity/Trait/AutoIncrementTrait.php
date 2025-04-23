<?php

namespace NewDavis\DatabaseManagement\Core\Entity\Trait;

trait AutoIncrementTrait
{
    protected ?int $auto_increment;

    /**
     * @return int|null
     */
    public function getAutoIncrement(): ?int
    {
        return $this->auto_increment;
    }
}