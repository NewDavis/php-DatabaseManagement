<?php

namespace NewDavis\DatabaseManagement\Util\Helper;

use NewDavis\DatabaseManagement\Entity\AbstractEntity;
use NewDavis\DatabaseManagement\Entity\AbstractEntityCollection;
use NewDavis\DatabaseManagement\Entity\EntityDefinitionInterface;
use NewDavis\DatabaseManagement\Entity\EntityInterface;
use NewDavis\DatabaseManagement\Entity\Field\Relational\ManyToManyRelation;

class EntityHelper
{
    public static function createEmptyEntity(EntityDefinitionInterface $definition): ?AbstractEntity
    {
        return new ($definition->getEntityClass())();
    }

    public static function createCollection(EntityDefinitionInterface $definition, array|EntityInterface $entities = []): ?AbstractEntityCollection
    {
        $collection = new ($definition->getCollectionClass())();

        if ($entities instanceof EntityInterface) {
            $collection->add($entities);
        }else{
            foreach ($entities as $entity) {
                $collection->add($entity);
            }
        }

        return $collection;
    }

    public static function isUniform(array $rows): bool
    {
        if (empty($rows)) {
            return true;
        }

        $firstKeys = array_keys($rows[0]);
        $firstKeys = array_diff($firstKeys, ['id']);
        sort($firstKeys);

        foreach ($rows as $row) {
            $keys = array_keys($row);
            $keys = array_diff($keys, ['id']);
            sort($keys);

            if ($keys !== $firstKeys) {
                return false;
            }
        }

        return true;
    }

    public static function getUniformColumns(array $rows): array
    {
        $keys = array_keys($rows[0]);
        return array_values(array_diff($keys, ['id']));
    }

    public static function groupByColumns(array $rows): array
    {
        $groups = [];

        foreach ($rows as $row) {
            $columns = array_keys($rows[0]);
            $columns = array_diff($columns, ['id']);
            sort($columns);

            $groupKey = implode('|', $columns);

            $groups[$groupKey][] = $row;
        }

        return $groups;
    }

}
