<?php

namespace NewDavis\DatabaseManagement\Entity;

use NewDavis\DatabaseManagement\Entity\Exception\Write\VariableForInternalNameNotFoundException;

abstract class AbstractEntity implements EntityInterface
{
    public function get(string $internalName): mixed
    {
        if (($property = EntityReflectionCache::getProperty($this, $internalName)) == null) {
            throw new VariableForInternalNameNotFoundException($internalName, self::class);
        }

        if (!$property->isInitialized($this)) {
            return null;
        }

        return $property->getValue($this);
    }

    public function set(string $internalName, mixed $value): EntityInterface
    {
        if (($property = EntityReflectionCache::getProperty($this, $internalName)) == null) {
            throw new VariableForInternalNameNotFoundException($internalName, self::class);
        }

        $property->setValue($this, $value);

        return $this;
    }
}
