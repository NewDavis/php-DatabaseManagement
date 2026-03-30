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
        $this->collectEntities();

        if ($checkExistence) {
            $this->checkExistence();
        }
    }

    private function collectEntities()
    {
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
                    $this->cache[$value::getDefinitionClass()][$value->getId()->toString()] = $value;
                } else if ($value instanceof AbstractEntityCollection) {
                    foreach ($value as $relatedEntity) {
                        $this->cache[$value::getDefinitionClass()][$relatedEntity->getId()->toString()] = $relatedEntity;
                    }
                }
            }

            $this->cache[$entity::getDefinitionClass()][$entity->getId()->toString()] = $entity;
        }
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
        foreach ($this->cache as $definitionClass => $entities) {
            if ($this->definition::class == $definitionClass && $this->action == WriteAction::CREATE) {
                // skip root entity on action CREATE.
                continue;
            }

            $repository = $this->registry->getRepositoryByDefinitionClass($definitionClass);

            $criteria = new Criteria(array_keys($entities));

            $foundIds = $repository->searchIds($criteria);

            $this->existence[$definitionClass] = $foundIds->getIds();
        }
    }
}