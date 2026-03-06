<?php

namespace NewDavis\DatabaseManagement\Entity;

interface EntityDefinitionInterface
{
    public function getEntityName(): string;
    /** @return FieldCollection */
    public function getFields(): FieldCollection;
    public function getEntityClass(): string;
    public function getCollectionClass(): string;
}