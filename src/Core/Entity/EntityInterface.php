<?php

namespace NewDavis\DatabaseManagement\Core\Entity;

interface EntityInterface
{

    public static function getDefinitionClass(): string|null;

}