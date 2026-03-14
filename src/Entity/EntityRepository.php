<?php

namespace NewDavis\DatabaseManagement\Entity;

use NewDavis\DatabaseManagement\Entity\Builder\Write\WriteBuilder;
use NewDavis\DatabaseManagement\Entity\Search\Criteria\Criteria;
use NewDavis\DatabaseManagement\Entity\Write\EntityWriteResult;
use NewDavis\DatabaseManagement\Util\Helper\EntityHelper;

class EntityRepository implements EntityRepositoryInterface
{
    private readonly WriteBuilder $writeBuilder;

    public function __construct(
        private readonly EntityDefinitionInterface $definition,
        private readonly EntityRegistry $registry,
    ) {
        $this->writeBuilder = new WriteBuilder($this->registry, $this->definition);
    }

    public function create(AbstractEntity|AbstractEntityCollection|array $entities): EntityWriteResult
    {
        $collection = $this->combineToCollection($entities);

        $statements = $this->writeBuilder->build($collection);

        $this->registry->getConnection()->write($statements);

        return new EntityWriteResult($collection);
    }

    public function update(AbstractEntity|AbstractEntityCollection|array $entities): EntityWriteResult
    {
        return new EntityWriteResult(EntityHelper::createCollection($this->definition));
    }

    public function upsert(AbstractEntity|AbstractEntityCollection|array $entities): EntityWriteResult
    {
        return new EntityWriteResult(EntityHelper::createCollection($this->definition));
    }

    public function delete(AbstractEntity|AbstractEntityCollection|array|Criteria $entities): EntityWriteResult
    {
        return new EntityWriteResult(EntityHelper::createCollection($this->definition));
    }

    private function combineToCollection(
        AbstractEntity|AbstractEntityCollection|array $entities
    ): AbstractEntityCollection {
        if (is_array($entities)) {
            return $this->getRegistry()->getConverter()->convertArrayToEntityCollection(
                $this->definition,
                $entities
            );
        } else if ($entities instanceof EntityInterface) {
            return EntityHelper::createCollection($this->definition, $entities);
        } else {
            return $entities;
        }
    }

    /**
     * @return EntityDefinitionInterface
     */
    public function getDefinition(): EntityDefinitionInterface
    {
        return $this->definition;
    }

    public function getRegistry(): EntityRegistry
    {
        return $this->registry;
    }
}
