<?php

namespace NewDavis\DatabaseManagement\Entity\Trait;

use Ramsey\Uuid\Uuid;

trait EntityIdTrait
{
    protected Uuid $id;

    /**
     * @return Uuid
     */
    public function getId(): Uuid
    {
        return $this->id;
    }
}
