<?php

namespace NewDavis\DatabaseManagement\Entity;

use NewDavis\DatabaseManagement\Entity\Exception\Write\VariableForInternalNameNotFoundException;
use NewDavis\DatabaseManagement\Entity\Field\Relational\ManyToOneRelation;
use NewDavis\DatabaseManagement\Entity\Field\Relational\OneToOneRelation;
use NewDavis\DatabaseManagement\Entity\Field\Relational\RelationalField;
use NewDavis\DatabaseManagement\Entity\Field\Serializable;
use NewDavis\DatabaseManagement\Entity\Field\StorableInterface;
use NewDavis\DatabaseManagement\Entity\Trait\EntityIdTrait;

abstract class AbstractEntity implements EntityInterface
{
    use EntityIdTrait;

    public function get(Serializable $serializable, string $internalName): mixed
    {
        if (($property = EntityReflectionCache::getProperty($this, $internalName)) == null) {
            throw new VariableForInternalNameNotFoundException($internalName, self::class);
        }

        if (!$property->isInitialized($this)) {
            return $serializable->getSerializer()->encode(
                null
            );
        }

        return $serializable->getSerializer()->encode(
            $property->getValue($this)
        );
    }

    public function set(Serializable $serializable, string $internalName, mixed $value): EntityInterface
    {
        if (($property = EntityReflectionCache::getProperty($this, $internalName)) == null) {
            throw new VariableForInternalNameNotFoundException($internalName, self::class);
        }

        $decoded = $serializable->getSerializer()->decode($value);
        if (
            ($serializable instanceof ManyToOneRelation || $serializable instanceof OneToOneRelation) &&
            $serializable->getForeignKey() != null
        ) {
            /** @var AbstractEntity $decoded */
            $this->set(
                $serializable->getForeignKey(),
                $serializable->getForeignKey()->getInternalName(),
                $decoded->getId()
            );
        }

        $property->setValue(
            $this,
            $decoded
        );

        return $this;
    }
}
