<?php

namespace NewDavis\DatabaseManagement\Core\Entity;

interface EntityInterface
{
    static function getDefinitionClass(): string;
}