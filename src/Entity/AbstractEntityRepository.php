<?php

namespace NewDavis\DatabaseManagement\Entity;

use NewDavis\DatabaseManagement\Entity\Search\Criteria\Criteria;
use NewDavis\DatabaseManagement\Entity\Write\EntityWriteResult;

class AbstractEntityRepository implements EntityRepositoryInterface
{
    public function create(AbstractEntity|AbstractEntityCollection|array $entities): EntityWriteResult
    {
        return new EntityWriteResult();
    }

    public function update(AbstractEntity|AbstractEntityCollection|array $entities): EntityWriteResult
    {
        return new EntityWriteResult();
    }

    public function upsert(AbstractEntity|AbstractEntityCollection|array $entities): EntityWriteResult
    {
        return new EntityWriteResult();
    }

    public function delete(AbstractEntity|AbstractEntityCollection|array|Criteria $entities): EntityWriteResult
    {
        return new EntityWriteResult();
    }
}