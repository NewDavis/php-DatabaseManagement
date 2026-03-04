<?php

namespace NewDavis\DatabaseManagement\Entity\Write;

class EntityWriteResult
{
    public function __construct(
        private readonly bool $success = false
    ) {
    }

    /**
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->success;
    }
}