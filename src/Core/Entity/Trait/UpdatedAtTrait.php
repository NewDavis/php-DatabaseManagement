<?php

namespace NewDavis\DatabaseManagement\Core\Entity\Trait;

use DateTimeImmutable;

trait UpdatedAtTrait
{

    protected ?DateTimeImmutable $updated_at = null;

    public function setUpdatedAt(DateTimeImmutable $updatedAt): static
    {
        $this->updated_at = $updatedAt;

        return $this;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updated_at;
    }

}