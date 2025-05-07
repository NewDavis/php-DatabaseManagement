<?php

namespace NewDavis\DatabaseManagement\Core\Entity\Exception;

class UnableToFindMatchingFkFieldForRelatedFieldException extends \Exception
{

    public function __construct(string $storageName, string $definitionClass)
{
    parent::__construct(
        'Unable to find matching FkField for RelationField "' . $storageName . '" in "' . $definitionClass . '"',
        0,
        null
    );
}

}