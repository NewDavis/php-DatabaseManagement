<?php

namespace NewDavis\DatabaseManagement\Entity\Builder\Write;

use NewDavis\DatabaseManagement\Entity\AbstractEntity;
use NewDavis\DatabaseManagement\Entity\AbstractEntityCollection;
use NewDavis\DatabaseManagement\Entity\EntityDefinitionInterface;
use NewDavis\DatabaseManagement\Entity\EntityIdCollection;
use NewDavis\DatabaseManagement\Entity\EntityRegistry;
use NewDavis\DatabaseManagement\Entity\Field\Relational\RelationalField;
use NewDavis\DatabaseManagement\Entity\Read\Criteria\Criteria;
use Ramsey\Uuid\UuidInterface;

class EntityWriteCache
{
    private array $cache = [];
    /** @var array<string, EntityIdCollection> */
    private array $existence = [];

    public function __construct(
        private readonly WriteAction $action,
        private readonly EntityDefinitionInterface $definition,
        private readonly EntityRegistry $registry,
        private readonly AbstractEntityCollection $collection,
        bool $checkExistence = true
    ) {
        $this->cache = $this->collectEntities();

        if ($checkExistence) {
            $this->checkExistence();
        }
    }

    public function collectEntities(bool $topLevel = true): array
    {
        $entities = [];

        /** @var AbstractEntity $entity */
        foreach ($this->collection as $entity) {
            /** @var RelationalField $relationField */
            foreach ($this->definition->getFields()->filter(RelationalField::class) as $relationField) {
                $value = $entity->get(
                    $relationField,
                    $relationField->getInternalName()
                );

                if ($value == null) {
                    continue;
                }

                if ($value instanceof AbstractEntity) {
                    $entities[$value::getDefinitionClass()][$value->getId()->toString()] = $value;
                } else if ($value instanceof AbstractEntityCollection) {
                    foreach ($value as $relatedEntity) {
                        $entities[$value::getDefinitionClass()][$relatedEntity->getId()->toString()] = $relatedEntity;
                    }
                }
            }

            if ($topLevel) {
                $entities[$entity::getDefinitionClass()][$entity->getId()->toString()] = $entity;
            }
        }

        return $entities;
    }

    public function get(EntityDefinitionInterface $definition, UuidInterface $id): ?AbstractEntity
    {
        if (
            !array_key_exists($definition::class, $this->cache) ||
            !array_key_exists($id->toString(), $this->cache[$definition::class])
        ) return null;

        return $this->cache[$definition::class][$id->toString()];
    }

    public function exists(EntityDefinitionInterface $definition, UuidInterface $id): bool
    {
        if (!array_key_exists($definition::class, $this->existence)) return false;

        return $this->existence[$definition::class]->has($id);
    }

    private function getTopLevelIds()
    {
        return array_map(
            fn(AbstractEntity $entity) => $entity->getId()->toString(),
            $this->collection->getEntities()
        );
    }

    /**
     * @param EntityDefinitionInterface $definition
     * @return EntityIdCollection|null
     */
    public function getExistentIds(EntityDefinitionInterface $definition): ?EntityIdCollection
    {
        return $this->existence[$definition::class] ?? null;
    }

    public function checkExistence()
    {
        $topLevelIds = $this->getTopLevelIds();

        foreach ($this->cache as $definitionClass => $entities) {
            $scopedEntities = array_filter(
                $entities,
                fn(AbstractEntity $entity) => !($this->definition::class == $definitionClass && $this->action == WriteAction::CREATE) ||
                    !in_array($entity->getId()->toString(), $topLevelIds)
            );

            if (count($scopedEntities) == 0) {
                continue;
            }

            $repository = $this->registry->getRepositoryByDefinitionClass($definitionClass);

            $criteria = new Criteria(array_keys($scopedEntities));

            $foundIds = $repository->searchIds($criteria);

            $this->existence[$definitionClass] = $foundIds->getIds();
        }
    }
}
