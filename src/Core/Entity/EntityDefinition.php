<?php

namespace DatabaseManagement\Core\Entity;

class EntityDefinition
{

    public function getEntityName(): string|null
    {
        return null;
    }

    public function getEntityClass(): string|null
    {
        return null;
    }

    public function getCollectionClass(): string|null
    {
        return null;
    }

    public function getPropertyDefinition(): array
    {
        return [];
    }

}