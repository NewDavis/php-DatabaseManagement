<?php

namespace NewDavis\DatabaseManagement\Entity;

use NewDavis\DatabaseManagement\Entity\Exception\Write\VariableForInternalNameNotFoundException;
use NewDavis\DatabaseManagement\Entity\Field\Serializable;

abstract class AbstractEntity implements EntityInterface
{
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

        $property->setValue(
            $this,
            $serializable->getSerializer()->decode($value)
        );

        return $this;
    }
}
