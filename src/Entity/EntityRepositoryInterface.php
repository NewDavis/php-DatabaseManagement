<?php

namespace NewDavis\DatabaseManagement\Entity;

use NewDavis\DatabaseManagement\Entity\Search\Criteria\Criteria;
use NewDavis\DatabaseManagement\Entity\Write\EntityWriteResult;

interface EntityRepositoryInterface
{
    public function create(array|AbstractEntity|AbstractEntityCollection $entities): EntityWriteResult;
    public function update(array|AbstractEntity|AbstractEntityCollection $entities): EntityWriteResult;
    public function upsert(array|AbstractEntity|AbstractEntityCollection $entities): EntityWriteResult;
    public function delete(array|AbstractEntity|AbstractEntityCollection|Criteria $entities): EntityWriteResult;
    public function getDefinition(): EntityDefinitionInterface;
    public function getRegistry(): EntityRegistry;
}
