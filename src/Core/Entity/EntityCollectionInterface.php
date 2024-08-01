<?php

namespace NewDavis\DatabaseManagement\Core\Entity;

use NewDavis\DatabaseManagement\Core\Criteria\Criteria;

interface EntityCollectionInterface
{

    public function set(array $entities) : void;
    public function clear() : void;
    public function add(Entity $entity) : void;
    public function remove(Entity $entity) : void;
    public function contains(Entity $entity) : bool;
    public function first() : Entity|null;
    public function firstId() : string|null;
    public function search(Criteria $criteria) : EntityCollection;
    public function searchBy(string $property, mixed $value) : EntityCollection;
    public function searchId($id) : string|null;
    public function count() : int;
    public function getEntities() : array;

}