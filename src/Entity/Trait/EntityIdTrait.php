<?php

namespace NewDavis\DatabaseManagement\Entity\Trait;

use Ramsey\Uuid\UuidInterface;

trait EntityIdTrait
{
    protected UuidInterface $id;

    /**
     * @return UuidInterface
     */
    public function getId(): UuidInterface
    {
        return $this->id;
    }
}
