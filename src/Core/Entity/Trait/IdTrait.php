<?php

namespace DatabaseManagement\Core\Entity\Trait;

trait IdTrait
{

    protected ?string $id;

    public function setId(string $id): static
    {
        $this->id = $id;
        return $this;
    }

    public function getId(): string
    {
        return $this->id;
    }

}