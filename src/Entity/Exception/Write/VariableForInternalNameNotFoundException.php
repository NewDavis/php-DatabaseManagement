<?php

namespace NewDavis\DatabaseManagement\Entity\Exception\Write;

class VariableForInternalNameNotFoundException extends \Exception
{
    public function __construct(string $internalName, string $className)
    {
        parent::__construct(
            "There is no property found for {$internalName} in {$className}."
        );
    }
}
