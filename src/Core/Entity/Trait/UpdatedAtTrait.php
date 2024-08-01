<?php

namespace DatabaseManagement\Core\Entity\Trait;

trait UpdatedAtTrait
{

    protected ?float $updated_at = null;

    public function setUpdatedAt(float $updatedAt): static
    {
        $this->updated_at = $updatedAt;
        return $this;
    }

    public function getUpdatedAt(): float
    {
        return $this->updated_at;
    }

}