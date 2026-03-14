<?php

namespace NewDavis\DatabaseManagement\Entity;

class EntityReflectionCache
{
    private static array $reflectionClasses = [];
    private static array $properties = [];

    public static function getReflectionClass(AbstractEntity $entity): ?\ReflectionClass
    {
        if (array_key_exists($entity::class, self::$reflectionClasses)) {
            return self::$reflectionClasses[$entity::class];
        }

        try {
            return self::$reflectionClasses[$entity::class] = new \ReflectionClass($entity);
        } catch (\ReflectionException $e) {
            return null;
        }
    }

    public static function getProperty(AbstractEntity $entity, string $internalName): ?\ReflectionProperty
    {
        $key = $entity::class . '.' . $internalName;

        if (array_key_exists($key, self::$properties)) {
            return self::$properties[$key];
        }

        try {
            $reflectionClass = self::getReflectionClass($entity);

            if ($reflectionClass == null) return null;

            return self::$properties[$key] = $reflectionClass->getProperty($internalName);
        } catch (\ReflectionException $e) {
            return null;
        }
    }
}