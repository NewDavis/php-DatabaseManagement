<?php

namespace NewDavis\DatabaseManagement\Core\Entity\Flag;

class Unique implements Flag
{
    public static function isInline(): bool
    {
        return false;
    }

    public static function getKeyword(): string
    {
        return 'ADD UNIQUE (%s)';
    }
}