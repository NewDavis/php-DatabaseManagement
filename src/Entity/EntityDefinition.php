<?php

namespace NewDavis\DatabaseManagement\Entity;

use NewDavis\DatabaseManagement\Entity\Field\Field;

interface EntityDefinition
{
    public static function getEntityName(): string;
    /** @return Field[] */
    public static function getFields(): array;
    public static function getEntityClass(): string;
    public static function getCollectionClass(): string;
}