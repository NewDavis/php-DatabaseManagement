<?php

namespace NewDavis\DatabaseManagement\Entity\Write;

use NewDavis\DatabaseManagement\Entity\AbstractEntity;
use NewDavis\DatabaseManagement\Entity\Exception\Write\EntityDefinitionMissesEntityClassMethodException;

class EntityConverter
{
    public static function createEntity(string $definition): ?AbstractEntity
    {
        if (!in_array('getEntityClass', get_class_methods($definition))) {
            throw new EntityDefinitionMissesEntityClassMethodException($definition);
        }

        $entityClass = $definition::getEntityClass();
    }

    public static function convertArrayToEntity(string $definition, array $entityData): AbstractEntity
    {

    }
}