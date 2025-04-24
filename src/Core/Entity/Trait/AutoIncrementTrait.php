<?php

namespace NewDavis\DatabaseManagement\Core\Entity\Trait;

trait AutoIncrementTrait
{
    protected ?int $autoIncrement;

    /**
     * @return int|null
     */
    public function getAutoIncrement(): ?int
    {
        return $this->autoIncrement;
    }
}