<?php

namespace NewDavis\DatabaseManagement\Core\Search\Criteria\Filter;

use NewDavis\DatabaseManagement\Core\Schema\SchemaBuilder;
use NewDavis\DatabaseManagement\Core\Search\Criteria\Filter\Exception\EmptySearchedValueException;
use NewDavis\DatabaseManagement\Core\Search\Criteria\Filter\Exception\UnknownInternalNameException;

class EqualsAnyFilter extends AbstractFilter
{
    /**
     * @param string $internalName
     * @param mixed[] $searchedValues
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
     * @return array
     */
    public function getSearchedValues(): array
    {
        return $this->searchedValues;
    }

    /**
     * @param string $definition
     * @return FilterResult
     * @throws UnknownInternalNameException|EmptySearchedValueException
     */
    public function convert(string $definition): FilterResult
    {
        if(count($this->getSearchedValues()) == 0) {
            throw new EmptySearchedValueException($this->getInternalName());
        }

        $fields = SchemaBuilder::filterFieldsByInternalName($definition, $this->getInternalName());

        // check if internalName is in the Definition
        if(count($fields) == 0) {
            throw new UnknownInternalNameException($definition, $this->getInternalName());
        }

        $field = $fields[0];

        $result = new FilterResult();
        $searchedValuesPlaceholder = array_map(
            function ($searchedValue) use ($result) {
                $result->addParameter('?', sprintf(
                    "%s",
                    $searchedValue
                ));

                return '?';
            },
            $this->getSearchedValues()
        );

        $result->setCondition(sprintf(
            "`%s`.`%s` IN (%s)",
            $definition::getEntityName(),
            $field->getStorageName(),
            rtrim(implode(', ', $searchedValuesPlaceholder), ', ')
        ));

        return $result;
    }
}