<?php

namespace NewDavis\DatabaseManagement\Core\Search\Criteria\Filter\Exception;

class UnknownMultiFilterOperatorException extends \Exception
{

    public function __construct(string $operator)
    {
        parent::__construct(
            'The provided operator "' . $operator . '" is not known!',
            0,
            null
        );
    }

}