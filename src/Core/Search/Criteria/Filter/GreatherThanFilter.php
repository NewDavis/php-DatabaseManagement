<?php

namespace NewDavis\DatabaseManagement\Core\Search\Criteria\Filter;

use NewDavis\DatabaseManagement\Core\Schema\SchemaBuilder;
use NewDavis\DatabaseManagement\Core\Search\Criteria\Filter\Exception\UnknownInternalNameException;

class GreatherThanFilter extends AbstractFilter
{
    /**
     * @param string $internalName
     * @param mixed $searchedValue
     */
    public function __construct(
        private readonly string $internalName,
        private readonly mixed $searchedValue,
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
     * @return mixed
     */
    public function getSearchedValue(): mixed
    {
        return $this->searchedValue;
    }

    /**
     * @param string $definition
     * @return FilterResult
     * @throws UnknownInternalNameException
     */
    public function convert(string $definition): FilterResult
    {
        $fields = SchemaBuilder::filterFieldsByInternalName($definition, $this->getInternalName());

        // check if internalName is in the Definition
        if(count($fields) == 0 && !str_contains($this->getInternalName(), '.')) {
            throw new UnknownInternalNameException($definition, $this->getInternalName());
        }

        $result = new FilterResult();

        $result->addParameter('?', sprintf(
            "%s",
            $this->getSearchedValue()
        ));
        if (str_contains($this->getInternalName(), '.')) {
            // is deepsearch
            $this->handleDeepSearch($definition, $result, "`%s`.`%s` > ?");
        } else {
            $field = $fields[0];

            $result->setCondition(sprintf(
                "`%s`.`%s` > ?",
                $definition::getEntityName(),
                $field->getStorageName()
            ));
        }

        return $result;
    }
}