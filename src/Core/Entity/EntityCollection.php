<?php

namespace NewDavis\DatabaseManagement\Core\Entity;

use NewDavis\DatabaseManagement\Core\Criteria\Criteria;
use NewDavis\DatabaseManagement\Core\Criteria\Filter\EqualsAnyFilter;
use NewDavis\DatabaseManagement\Core\Criteria\Filter\EqualsFilter;
use Generator;
use IteratorAggregate;
use NewDavis\DatabaseManagement\Core\Criteria\Filter\NotEqualsFilter;
use ReflectionClass;
use ReturnTypeWillChange;

class EntityCollection implements \Countable, IteratorAggregate, EntityCollectionInterface
{

    private array $entities;

    public function __construct(Entity... $entities)
    {
        $this->entities = $entities;
    }

    public function set(array $entities) : void
    {
        $this->clear();

        foreach ($entities as $entity) {
            $this->entities[$entity->getId()] = $entity;
        }
    }

    public function clear() : void
    {
        $this->entities = [];
    }

    public function add(Entity $entity) : void
    {
        $this->entities[$entity->getId()] = $entity;
    }

    public function remove(Entity $entity) : void
    {
        unset($this->entities[$entity->getId()]);
    }

    public function contains(Entity $entity) : bool
    {
        return array_key_exists($entity->getId(), $this->entities);
    }

    public function first() : Entity|null
    {
        if(count($this->entities) == 0) return null;

        $firstKey = array_keys($this->entities)[0];

        return $this->entities[$firstKey];
    }

    public function firstId() : string|null
    {
        if (count($this->entities) == 0) return null;

        $firstKey = array_keys($this->entities)[0];

        return $this->entities[$firstKey]->getId();
    }

    public function search(Criteria $criteria) : EntityCollection
    {
        $collection = new EntityCollection();

        foreach ($this->getEntities() as $entity) {
            foreach ($criteria->getFilters() as $filter) {
                $success = true;

                $reflectionEntity = new ReflectionClass($entity);

                switch (get_class($filter)) {
                    case EqualsFilter::class:
                        $reflectionProperty = $reflectionEntity->getProperty($filter->getProperty());

                        if (!$reflectionProperty->isInitialized($entity)) {
                            $success = false;
                            break;
                        }

                        $reflectionPropertyValue = $reflectionProperty->getValue($entity);

                        if ($filter->getValue() !== $reflectionPropertyValue) {
                            $success = false;
                            break;
                        }
                        break;
                    case EqualsAnyFilter::class:
                        $reflectionProperty = $reflectionEntity->getProperty($filter->getProperty());

                        if (!$reflectionProperty->isInitialized($entity)) {
                            $success = false;
                            break;
                        }

                        $reflectionPropertyValue = $reflectionProperty->getValue($entity);

                        foreach ($filter->getValues() as $value) {
                            if ($value !== $reflectionPropertyValue) {
                                $success = false;
                                break;
                            }
                        }
                        break;
                    case NotEqualsFilter::class:
                        $reflectionProperty = $reflectionEntity->getProperty($filter->getProperty());

                        if (!$reflectionProperty->isInitialized($entity)) {
                            $success = false;
                            break;
                        }

                        $reflectionPropertyValue = $reflectionProperty->getValue($entity);

                        if ($filter->getValue() === $reflectionPropertyValue) {
                            $success = false;
                            break;
                        }
                        break;
                }

                if($criteria->getLimit() > 0 && $collection->count() >= $criteria->getLimit()) {
                    return $collection;
                }

                if($success) {
                    $collection->add($entity);
                }
            }
        }

        return $collection;
    }

    public function searchBy(string $property, mixed $value) : EntityCollection
    {
        $criteria = new Criteria();
        if(is_array($value)) {
            $criteria->addFilter(new EqualsAnyFilter($property, $value));
        }else{
            $criteria->addFilter(new EqualsFilter($property, $value));
        }

        return $this->search($criteria);
    }

    public function searchId($id) : string|null
    {
        if(!array_key_exists($id, $this->entities)) return null;

        return $this->entities[$id]->getId();
    }

    public function count(): int
    {
        return count($this->entities);
    }

    /**
     * @return array
     */
    public function getEntities(): array
    {
        return $this->entities;
    }

    #[ReturnTypeWillChange]
    public function getIterator(): Generator
    {
        yield from $this->entities;
    }

}