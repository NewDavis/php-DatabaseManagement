<?php

namespace DatabaseManagement\Core\Entity;

use DatabaseManagement\Core\Criteria\Criteria;

interface EntityCollectionInterface
{

    public function search(Criteria $criteria) : EntityCollection;
    public function searchId($id) : string|null;
    public function count() : int;

}