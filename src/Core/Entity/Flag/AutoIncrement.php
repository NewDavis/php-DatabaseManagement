<?php

namespace NewDavis\DatabaseManagement\Core\Entity\Flag;

class AutoIncrement implements Flag
{
    public static function isInline(): bool
    {
        return false;
    }

    public static function getKeyword(): string
    {
        return 'AUTO_INCREMENT';
    }

}