<?php

namespace NewDavis\DatabaseManagement\Entity\Builder\Write;

use NewDavis\DatabaseManagement\Entity\AbstractEntity;
use NewDavis\DatabaseManagement\Entity\AbstractEntityCollection;
use NewDavis\DatabaseManagement\Entity\EntityDefinitionInterface;
use NewDavis\DatabaseManagement\Entity\EntityRegistry;
use NewDavis\DatabaseManagement\Entity\Field\Relational\RelationalField;

class EntityWriteCache
{
    private array $cache = [];
    private array $existent = [];

    public function __construct(
        private readonly WriteAction $action,
        private readonly EntityDefinitionInterface $definition,
        private readonly EntityRegistry $registry,
        private readonly AbstractEntityCollection $collection
    ) {
        $this->collectEntities();
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

    public function checkExistence()
    {

    }
}