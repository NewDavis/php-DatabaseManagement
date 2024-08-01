<?php

namespace NewDavis\DatabaseManagement\Core\Entity\Trait;

trait CreatedAtTrait
{

    protected ?float $created_at;

    public function setCreatedAt(float $createdAt): static
    {
        $this->created_at = $createdAt;
        return $this;
    }

    public function getCreatedAt(): float
    {
        return $this->created_at;
    }

}