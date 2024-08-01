<?php

namespace NewDavis\DatabaseManagement\Core\Entity;

interface EntityDefinitionInterface
{

    public function getEntityName(): string|null;
    public function getEntityClass(): string|null;
    public function getCollectionClass(): string|null;
    public function getPropertyDefinition() : array;

}