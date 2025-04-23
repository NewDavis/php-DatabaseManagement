<?php

namespace NewDavis\DatabaseManagement\Core\Search\Criteria\Filter;

use NewDavis\DatabaseManagement\Core\Schema\SchemaBuilder;
use NewDavis\DatabaseManagement\Core\Search\Criteria\Filter\Exception\UnknownInternalNameException;

class GreatherThanFilter implements Filter
{
    /**
     * @param string $internalName
     * @param string $searchedValue
     */
    public function __construct(
        private readonly string $internalName,
        private readonly string $searchedValue,
    ) {
    }

    /**
     * @return string
     */
    public function getInternalName(): string
    {
        return $this->internalName;
    }

    /**
     * @return string
     */
    public function getSearchedValue(): string
    {
        return $this->searchedValue;
    }

    /**
     * @param string $definition
     * @return string
     * @throws UnknownInternalNameException
     */
    public function convert(string $definition): string
    {
        $fields = SchemaBuilder::filterFieldsByInternalName($definition, $this->getInternalName());

        // check if internalName is in the Definition
        if(count($fields) == 0) {
            throw new UnknownInternalNameException($this->getInternalName());
        }

        $field = $fields[0];

        return sprintf(
            "`%s`.`%s` > '%s'",
            $definition::getEntityName(),
            $field->getStorageName(),
            $this->getSearchedValue()
        );
    }
}