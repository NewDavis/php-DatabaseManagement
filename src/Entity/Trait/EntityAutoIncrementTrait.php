<?php

namespace NewDavis\DatabaseManagement\Entity\Trait;

trait EntityAutoIncrementTrait
{
    protected int $autoIncrement;

    /**
     * @return int
     */
    public function getAutoIncrement(): int
    {
        return $this->autoIncrement;
    }
}
