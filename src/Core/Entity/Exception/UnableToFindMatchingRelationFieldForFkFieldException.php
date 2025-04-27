<?php

namespace NewDavis\DatabaseManagement\Core\Entity\Exception;

class UnableToFindMatchingRelationFieldForFkFieldException extends \Exception
{

    public function __construct(string $storageName, string $definitionClass)
{
    parent::__construct(
        'Unable to find matching RelationField for FkField "' . $storageName . '" in "' . $definitionClass . '"',
        0,
        null
    );
}

}