<?php

namespace NewDavis\DatabaseManagement\Entity;

interface EntityDefinitionInterface
{
    public static function getEntityName(): string;
    /** @return FieldCollection */
    public static function getFields(): FieldCollection;
    public static function getEntityClass(): string;
    public static function getCollectionClass(): string;
}