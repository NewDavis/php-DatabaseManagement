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
}
