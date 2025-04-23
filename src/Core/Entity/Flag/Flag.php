<?php

namespace NewDavis\DatabaseManagement\Core\Entity\Flag;

interface Flag
{
    /**
     * @return bool
     */
    public static function isInline(): bool;

    /**
     * @return string
     */
    public static function getKeyword(): string;
}