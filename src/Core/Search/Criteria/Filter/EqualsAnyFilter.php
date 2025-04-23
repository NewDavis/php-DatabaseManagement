<?php

namespace NewDavis\DatabaseManagement\Core\Search\Criteria\Filter;

use NewDavis\DatabaseManagement\Core\Schema\SchemaBuilder;
use NewDavis\DatabaseManagement\Core\Search\Criteria\Filter\Exception\EmptySearchedValueException;
use NewDavis\DatabaseManagement\Core\Search\Criteria\Filter\Exception\UnknownInternalNameException;

class EqualsAnyFilter implements Filter
{
    /**
     * @param string $internalName
     * @param string[] $searchedValues
     */
    public function __construct(
        private readonly string $internalName,
        private readonly array $searchedValues,
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
     * @return array<string>
     */
    public function getSearchedValues(): array
    {
        return $this->searchedValues;
    }

    /**
     * @param string $definition
     * @return string
     * @throws UnknownInternalNameException
     */
    public function convert(string $definition): string
    {
        if(count($this->getSearchedValues()) == 0) {
            throw new EmptySearchedValueException($this->getInternalName());
        }

        $fields = SchemaBuilder::filterFieldsByInternalName($definition, $this->getInternalName());

        // check if internalName is in the Definition
        if(count($fields) == 0) {
            throw new UnknownInternalNameException($this->getInternalName());
        }

        $field = $fields[0];

        return sprintf(
            "`%s`.`%s` IN ('%s')",
            $definition::getEntityName(),
            $field->getStorageName(),
            rtrim(implode("', '", $this->getSearchedValues()), ", '")
        );
    }
}