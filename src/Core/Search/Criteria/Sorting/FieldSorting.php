<?php

namespace NewDavis\DatabaseManagement\Core\Search\Criteria\Sorting;

use NewDavis\DatabaseManagement\Core\Schema\SchemaBuilder;
use NewDavis\DatabaseManagement\Core\Search\Criteria\Filter\Exception\UnknownInternalNameException;

class FieldSorting extends Sorting
{
    public function __construct(
        private readonly string $internalName,
        private readonly string $direction
    ) {
    }

    /**
     * @return string
     */
    public function getBy($definition): string
    {
        $fields = SchemaBuilder::filterFieldsByInternalName($definition, $this->internalName);

        // check if internalName is in the Definition
        if(count($fields) == 0) {
            throw new UnknownInternalNameException($this->internalName);
        }

        return $fields[0]->getStorageName();
    }

    /**
     * @return string
     */
    public function getDirection(): string
    {
        return $this->direction;
    }

}