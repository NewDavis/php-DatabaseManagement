<?php

namespace NewDavis\DatabaseManagement\Entity;

use NewDavis\DatabaseManagement\Entity\Exception\Write\VariableForInternalNameNotFoundException;

abstract class AbstractEntity implements EntityInterface
{
    private readonly \ReflectionClass $reflectionClass;

    public function __construct()
    {
        $this->reflectionClass = new \ReflectionClass($this);
    }

    public function set(string $internalName, mixed $value): EntityInterface
    {
        if (!$this->reflectionClass->hasProperty($internalName)) {
            throw new VariableForInternalNameNotFoundException($internalName, self::class);
        }

        $property = $this->reflectionClass->getProperty($internalName);
        $property->setValue($this, $value);

        return $this;
    }
}
