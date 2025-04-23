<?php

namespace NewDavis\DatabaseManagement\Core\Entity;

use NewDavis\DatabaseManagement\Core\Entity\Field\Field;

interface EntityDefinition
{
    static function getEntityName(): string;
    static function getEntityClass(): string;
    static function getCollectionClass(): string;
    /** @return Field[] */
    static function getDefinitionFields(): array;
}