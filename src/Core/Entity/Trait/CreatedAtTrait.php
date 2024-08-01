<?php

namespace NewDavis\DatabaseManagement\Core\Entity\Trait;

use DateTimeImmutable;

trait CreatedAtTrait
{

    protected ?DateTimeImmutable $created_at;

    public function setCreatedAt(DateTimeImmutable $createdAt): static
    {
        $this->created_at = $createdAt;

        return $this;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->created_at;
    }

}