<?php

namespace NewDavis\DatabaseManagement\Entity\Exception\Search;

class UnableToFindMatchingRelationalFieldByInternalName extends \Exception
{
    public function __construct(string $internalName)
    {
        parent::__construct("Unable to find matching relational field for internal name '$internalName'.");
    }
}