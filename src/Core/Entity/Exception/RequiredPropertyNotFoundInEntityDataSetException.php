<?php

namespace NewDavis\DatabaseManagement\Core\Entity\Exception;

class RequiredPropertyNotFoundInEntityDataSetException extends \Exception
{

    public function __construct(string $internalName)
    {
        parent::__construct(
            'There is a required property missing with the name "' . $internalName . '" in the received dataset',
            0,
            null
        );
    }

}