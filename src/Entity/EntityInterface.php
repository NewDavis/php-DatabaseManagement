<?php

namespace NewDavis\DatabaseManagement\Entity;

interface EntityInterface
{
    public static function getDefinitionClass(): string;
    public function set(string $internalName, mixed $value): EntityInterface;
}
