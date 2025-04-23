<?php

namespace NewDavis\DatabaseManagement\Core\Entity\Trait;

use DateTimeImmutable;

trait CreatedAtTrait
{
    protected ?DateTimeImmutable $createdAt;

    public function setCreatedAt(DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
}