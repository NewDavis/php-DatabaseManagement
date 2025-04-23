<?php

namespace NewDavis\DatabaseManagement\Core\Entity\Trait;

use DateTimeImmutable;

trait UpdatedAtTrait
{
    protected ?DateTimeImmutable $updatedAt;

    public function setUpdatedAt(DateTimeImmutable $createdAt): static
    {
        $this->updatedAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
}