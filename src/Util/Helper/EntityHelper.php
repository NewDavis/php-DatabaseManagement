<?php

namespace NewDavis\DatabaseManagement\Util\Helper;

use NewDavis\DatabaseManagement\Entity\AbstractEntity;
use NewDavis\DatabaseManagement\Entity\AbstractEntityCollection;
use NewDavis\DatabaseManagement\Entity\EntityCollectionInterface;
use NewDavis\DatabaseManagement\Entity\EntityDefinitionInterface;
use NewDavis\DatabaseManagement\Entity\EntityInterface;
use NewDavis\DatabaseManagement\Entity\Field\Relational\ManyToManyRelation;

class EntityHelper
{
    public static function createEmptyEntity(EntityDefinitionInterface $definition): ?AbstractEntity
    {
        return new ($definition->getEntityClass())();
    }

    public static function createCollection(
        EntityDefinitionInterface $definition,
        array|EntityInterface $entities = []
    ): ?AbstractEntityCollection {
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

    public static function createCollectionByCollectionClass(
        string $collectionClass,
        array|EntityInterface $entities = []
    ): ?AbstractEntityCollection {
        $collection = new ($collectionClass)();

        if ($entities instanceof EntityInterface) {
            $collection->add($entities);
        }else{
            foreach ($entities as $entity) {
                $collection->add($entity);
            }
        }

        return $collection;
    }

    /**
     * Only works if the Definition and Collection are in the same directory and named identically. E.g. AccountDefinition and AccountCollection
     * @param string $definitionClass
     * @return string|null
     */
    public static function findSuitableCollectionClassByDefinitionClass(string $definitionClass): ?string
    {
        if (!str_ends_with($definitionClass, 'Definition')) return null;

        $parts = explode('\\', $definitionClass);
        $last = array_key_last($parts);

        $parts[$last] = str_replace('Definition', 'Collection', $parts[$last]);

        return implode('\\', $parts);
    }

    public static function groupByColumns(array $rows): array
    {
        $groups = [];

        foreach ($rows as $row) {
            if (array_key_exists('entity', $row)) {
                unset($row['entity']);
            }

            $columns = array_keys($row);
            if (count(array_diff($columns, ['id'])) == 0) continue;

            sort($columns);

            $groupKey = implode('|', $columns);

            $groups[$groupKey][] = $row;
        }

        return $groups;
    }

}
