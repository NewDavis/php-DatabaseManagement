<?php

namespace DatabaseManagement\Core\Entity;

interface EntityInterface
{

    public function getDefinitionClass(): string|null;

    public function jsonSerialize(): array;

}