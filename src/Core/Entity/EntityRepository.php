<?php

namespace NewDavis\DatabaseManagement\Core\Entity;

use NewDavis\DatabaseManagement\Core\Criteria\Criteria;
use NewDavis\DatabaseManagement\Core\Driver\Connection;
use NewDavis\DatabaseManagement\Core\Entity\Property\Property;
use NewDavis\DatabaseManagement\Core\Entity\Property\Relation\RelationProperty;
use NewDavis\DatabaseManagement\Schema\EntitySchemaBuilder;
use ReflectionClass;
use Symfony\Component\DependencyInjection\ContainerInterface;

class EntityRepository implements EntityRepositoryInterface
{

    private array $entities = [];
    private array $persistMemory = [];

    public function __construct(private readonly EntityDefinition   $entityDefinition,
                                private readonly Connection         $connection,
                                private readonly ContainerInterface $container)
    {}

    public function getEntityDefinition(): EntityDefinition|null
    {
        return $this->entityDefinition;
    }

    public function create(Entity $entity)
    {
        $entitySchemaBuilder = EntitySchemaBuilder::getEntitySchemaBuilder($this, $entity, $this->container);

        $timestamp = round(microtime(true) * 1000);

        $this->persistMemory[$timestamp][] = $entitySchemaBuilder->create();
    }

    public function update(Entity $entity)
    {
        $entitySchemaBuilder = EntitySchemaBuilder::getEntitySchemaBuilder($this, $entity, $this->container);

        $timestamp = round(microtime(true) * 1000);

        $this->persistMemory[$timestamp][] = $entitySchemaBuilder->update();
    }

    public function delete(Entity $entity)
    {
        $entitySchemaBuilder = EntitySchemaBuilder::getEntitySchemaBuilder($this, $entity, $this->container);

        $timestamp = round(microtime(true) * 1000);

        $this->persistMemory[$timestamp][] = $entitySchemaBuilder->delete();
    }

    public function persist(Entity $entity)
    {
        $entitySchemaBuilder = EntitySchemaBuilder::getEntitySchemaBuilder($this, $entity, $this->container);

        if($entity->shouldDelete()) {
            $query = $entitySchemaBuilder->delete();
        }else if($entity->isPersisted()) {
            $query = $entitySchemaBuilder->update();
        }else{
            $query = $entitySchemaBuilder->create();
        }

        $timestamp = round(microtime(true) * 1000);

        $this->persistMemory[$timestamp][] = $query;
    }

    public function flush()
    {
        $this->connection->prepareQueries($this->persistMemory);
        $this->persistMemory = [];
    }

    public function search(Criteria $criteria): EntityCollection
    {
        $entitySchemaBuilder = EntitySchemaBuilder::getEntitySchemaBuilder($this, null, $this->container);

        $queryData = $entitySchemaBuilder->search($criteria);

        $result = $this->connection->prepare($queryData['query'], $queryData['parameters']);

        $collection = new EntityCollection();

        foreach ($result as $entities) {
            foreach ($entities as $entityData) {
                $entity = $this->loadEntity($entityData);

                if ($entity) {
                    $collection->add($entity);
                }
            }
        }

        return $collection;
    }

    public function loadEntity(array $entityData) : Entity|null
    {
        $key = $this->getEntityDefinition()->getEntityName() . '.' . $entityData['id'];

        /*if(array_key_exists($key, $this->entities)) {
            return $this->entities[$key];
        }*/

        $entity = $this->createEntityInstance();
        $reflectionEntity = new ReflectionClass($entity);

        foreach ($entityData as $property => $value) {
            if(str_ends_with($property, '_id')) continue;
            if(!isset($value)) continue;

            if(!$reflectionEntity->hasProperty($property)) {
                throw new \InvalidArgumentException("Property '$property' does not exist in class '". get_class($entity) . "'");
            }

            $reflectionProperty = $reflectionEntity->getProperty($property);

            $entityProperty = $this->getPropertyByName($property);

            if($entityProperty instanceof RelationProperty) {
                $result = [];

                $entityRepository = $this->getEntityRepositoryByProperty($entityProperty);

                if(json_validate($value)) {
                    $nestedEntities = json_decode($value, true);

                    foreach ($nestedEntities as $nestedEntityData) {
                        $nestedKey = $entityRepository->getEntityDefinition()->getEntityName() . '.' . $nestedEntityData['id'];
                        /*if(array_key_exists($nestedKey, $this->entities)) {
                            $result[] = $this->entities[$nestedKey];
                            continue;
                        }*/

                        $loadedEntity = $entityRepository->loadEntity($nestedEntityData);

                        $this->entities[$nestedKey] = $loadedEntity;

                        $result[] = $loadedEntity;
                    }
                }else{
                    $nestedKey = $entityRepository->getEntityDefinition()->getEntityName() . '.' . $nestedEntityData['id'];
                    /*if(array_key_exists($nestedKey, $this->entities)) {
                        $result[] = $this->entities[$nestedKey];
                    }else {*/
                        $loadedEntity = $entityRepository->loadEntity($value);

                        $this->entities[$nestedKey] = $loadedEntity;

                        $result[] = $loadedEntity;
                    /*}*/
                }

                // check if it's either a collection or entity.
                switch($reflectionProperty->getType()->getName()) {
                    case $entityRepository->getEntityDefinition()->getCollectionClass():
                        // is collection
                        $collection = $reflectionProperty->getValue($entity);

                        $collection->set($result);
                        $reflectionProperty->setValue($entity, $collection);
                        break;
                    case $entityRepository->getEntityDefinition()->getEntityClass():
                        // is entity
                        $reflectionProperty->setValue($entity, $result[0]);
                        break;
                }
            }else{
                $reflectionProperty->setValue($entity, $value);
            }
        }

        $this->entities[$key] = $entity;

        return $entity;
    }

    private function createEntityInstance() : Entity
    {
        $entityClass = $this->entityDefinition->getEntityClass();

        return new $entityClass(true);
    }

    private function getPropertyByName($propertyName) : Property|null
    {
        foreach ($this->entityDefinition->getPropertyDefinition() as $property) {
            if($property->getProperty() === $propertyName) return $property;
        }

        return null;
    }

    private function getEntityRepositoryByProperty(RelationProperty $relationProperty) : EntityRepository
    {
        return $this->container->get($relationProperty->getReferencedEntity() . '.repository');
    }

    public function searchAll(): EntityCollection
    {
        return $this->search(new Criteria());
    }

    public function searchIds(Criteria $criteria): array
    {
        $entitySchemaBuilder = EntitySchemaBuilder::getEntitySchemaBuilder($this, null, $this->container);

        $queryData = $entitySchemaBuilder->searchIds($criteria);

        $result = $this->connection->prepare($queryData['query'], $queryData['parameters']);

        $ids = [];

        foreach ($result as $queryResult) {
            foreach ($queryResult as $rows) {
                foreach ($rows as $id) {
                    $ids[] = $id;
                }
            }
        }

        return $ids;
    }

    public function count(Criteria $criteria): int
    {
        $entitySchemaBuilder = EntitySchemaBuilder::getEntitySchemaBuilder($this, null, $this->container);

        $queryData = $entitySchemaBuilder->searchCount($criteria);

        $result = $this->connection->prepare($queryData['query'], $queryData['parameters']);

        $count = 0;

        foreach ($result as $queryResult) {
            foreach ($queryResult as $rows) {
                foreach ($rows as $rowCount) {
                    $count += $rowCount;
                }
            }
        }

        return $count;
    }
}