<?php

namespace NewDavis\DatabaseManagement\Entity;

use NewDavis\DatabaseManagement\Entity\Builder\Table\TableBuilder;
use NewDavis\DatabaseManagement\Entity\Builder\Write\WriteBuilder;
use NewDavis\DatabaseManagement\Entity\Read\Criteria\Criteria;
use NewDavis\DatabaseManagement\Entity\Read\EntityCountResult;
use NewDavis\DatabaseManagement\Entity\Read\EntityIdSearchResult;
use NewDavis\DatabaseManagement\Entity\Read\EntitySearchResult;
use NewDavis\DatabaseManagement\Entity\Write\EntityWriteResult;

interface EntityRepositoryInterface
{
    public function search(Criteria $criteria): EntitySearchResult;
    public function searchIds(Criteria $criteria): EntityIdSearchResult;
    public function create(array|AbstractEntity|AbstractEntityCollection $entities): EntityWriteResult;
    public function update(array $entities): EntityWriteResult;
    public function upsert(array $entities): EntityWriteResult;
    public function delete(Criteria $criteria): EntityWriteResult;
    public function count(Criteria $criteria): EntityCountResult;
    public function getDefinition(): EntityDefinitionInterface;
    public function getRegistry(): EntityRegistry;
    public function getWriteBuilder(): WriteBuilder;
}
