<?php

namespace NewDavis\DatabaseManagement\Core\Search\Criteria\Filter\Exception;

class NoFilterPassedMultiFilterException extends \Exception
{

    public function __construct()
    {
        parent::__construct(
            'There is no Filter passed to MultiFilter!',
            0,
            null
        );
    }

}