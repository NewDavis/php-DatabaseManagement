<?php

namespace NewDavis\DatabaseManagement\Core\Search\Criteria\Filter\Exception;

class EmptySearchedValueException extends \Exception
{

    public function __construct(string $internalName)
    {
        parent::__construct(
            'The provided searchedValue for "' . $internalName . '" is not allowed to be empty',
            0,
            null
        );
    }

}