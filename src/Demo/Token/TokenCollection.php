<?php

namespace NewDavis\DatabaseManagement\Demo\Token;

use NewDavis\DatabaseManagement\Entity\AbstractEntityCollection;

class TokenCollection extends AbstractEntityCollection
{
    public static function getDefinitionClass(): string
    {
        return TokenDefinition::class;
    }
}