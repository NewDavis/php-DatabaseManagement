<?php

namespace NewDavis\DatabaseManagement\Core\Search\Criteria\Filter\Exception;

class UnknownInternalNameException extends \Exception
{

    public function __construct(string $definition, string $internalName)
    {
        parent::__construct(
            'Tried to access unknown internalName "' . $internalName . '" in "' . $definition . '"'
        );
    }

}