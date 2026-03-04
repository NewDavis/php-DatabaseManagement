<?php

namespace NewDavis\DatabaseManagement\Util\Helper;

class StringHelper
{
    public static function toCamelCase(string $string): string {
        return lcfirst(str_replace(' ', '', ucwords(preg_replace('/[^a-zA-Z0-9]+/', ' ', $string))));
    }
}