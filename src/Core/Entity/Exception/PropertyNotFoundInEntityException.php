<?php

namespace NewDavis\DatabaseManagement\Core\Entity\Exception;

class PropertyNotFoundInEntityException extends \Exception
{

    public function __construct(string $internalName, string $entityClass)
    {
        parent::__construct(
            'There is no property with the Name "' . $internalName . '" in "' . $entityClass . '"!',
            0,
            null
        );
    }

}