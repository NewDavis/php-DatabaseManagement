<?php

namespace NewDavis\DatabaseManagement\Demo\Token;

use NewDavis\DatabaseManagement\Entity\AbstractEntity;

class TokenEntity extends AbstractEntity
{
    public static function getDefinitionClass(): string
    {
        return TokenDefinition::class;
    }
}