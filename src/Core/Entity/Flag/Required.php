<?php

namespace NewDavis\DatabaseManagement\Core\Entity\Flag;

class Required implements Flag
{
    public static function isInline(): bool
    {
        return true;
    }

    public static function getKeyword(): string
    {
        return 'NOT NULL';
    }
}