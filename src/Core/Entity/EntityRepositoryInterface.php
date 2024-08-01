<?php

namespace NewDavis\DatabaseManagement\Core\Entity;

use NewDavis\DatabaseManagement\Core\Criteria\Criteria;

interface EntityRepositoryInterface
{

    public function getEntityDefinition() : EntityDefinition|null;

    public function persist(Entity $entity);

    public function flush();

    public function search(Criteria $criteria) : EntityCollection;

    public function searchAll() : EntityCollection;

    public function searchIds(Criteria $criteria) : array;

}