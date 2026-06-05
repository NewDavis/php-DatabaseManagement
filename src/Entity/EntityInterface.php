<?php

namespace NewDavis\DatabaseManagement\Entity;

use NewDavis\DatabaseManagement\Entity\Field\Serializable;

interface EntityInterface
{
    public static function getDefinitionClass(): string;
    public function get(Serializable $serializable, string $internalName): mixed;
    public function set(Serializable $serializable, string $internalName, mixed $value): EntityInterface;
}
