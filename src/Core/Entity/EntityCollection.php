<?php

namespace NewDavis\DatabaseManagement\Core\Entity;

use NewDavis\DatabaseManagement\Core\Search\Criteria\Criteria;

/**
 * @template TElement
 */
class EntityCollection
{

    /** @var TElement[] */
    private $entities;

    /**
     * @param TElement[] $entities
     * @return void
     */
    public function set(array $entities)
    {
        $this->entities = $entities;
    }

    /**
     * @return void
     */
    public function clear()
    {
        $this->entities = [];
    }

    /**
     * @param TElement $entity
     * @return void
     */
    public function add($entity)
    {
        $this->entities[] = $entity;
    }

    /**
     * @param TElement $entity
     * @return void
     */
    public function remove($entity)
    {
        $this->entities = array_filter($this->entities, fn($item) => $item !== $entity);
    }

    /**
     * @param TElement $entity
     * @return bool
     */
    public function contains($entity)
    {
        return false;
    }

    /**
     * @return TElement|null
     */
    public function first()
    {
        if(count($this->entities) === 0) return null;

        return $this->entities[0];
    }

    /**
     * @return string|null
     */
    public function firstId()
    {
        if(count($this->entities) === 0) return null;

        return $this->entities[0]->getId();
    }

    /**
     * @param Criteria $criteria
     * @return EntityCollection<TElement>
     */
    public function search(Criteria $criteria)
    {
        return new EntityCollection([]);
    }

    /**
     * @param string $property
     * @param mixed $value
     * @return EntityCollection<TElement>
     */
    public function searchBy(string $property, mixed $value)
    {
        return new EntityCollection([]);
    }

    /**
     * @param $id
     * @return string|null
     */
    public function searchId($id)
    {
        return null;
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->entities);
    }

    /**
     * @return TElement[]
     */
    public function getEntities()
    {
        return $this->entities;
    }
}