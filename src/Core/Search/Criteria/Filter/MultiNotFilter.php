<?php

namespace NewDavis\DatabaseManagement\Core\Search\Criteria\Filter;

use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use NewDavis\DatabaseManagement\Core\Search\Criteria\Filter\Exception\NoFilterPassedMultiFilterException;
use NewDavis\DatabaseManagement\Core\Search\Criteria\Filter\Exception\UnknownMultiFilterOperatorException;

class MultiNotFilter implements Filter
{
    const OPERATOR_AND = 'AND';
    const OPERATOR_OR = 'OR';

    /**
     * @param string $operator
     * @param array<Filter> $filter
     */
    public function __construct(
        private readonly string $operator,
        private readonly array $filter
    ) {}

    /**
     * @return string
     */
    public function getOperator(): string
    {
        return $this->operator;
    }

    /**
     * @return array
     */
    public function getFilter(): array
    {
        return $this->filter;
    }

    /**
     * @param string $definition
     * @return FilterResult
     */
    public function convert(string $definition): FilterResult
    {
        if(count($this->getFilter()) == 0) {
            throw new NoFilterPassedMultiFilterException();
        }

        if($this->getOperator() !== self::OPERATOR_OR &&
            $this->getOperator() !== self::OPERATOR_AND) {
            throw new UnknownMultiFilterOperatorException($this->getOperator());
        }

        $result = new FilterResult();
        $conditions = '';

        foreach ($this->getFilter() as $filter) {
            $converted = $filter->convert($definition);

            $conditions .= $converted->getCondition() . ' ' . $this->getOperator();
            foreach ($converted->getParameters() as $key => $value) {
                $result->addParameter($key, $value);
            }
        }

        $conditions = rtrim($conditions, ' ' . $this->getOperator());

        if($conditions === '') {
            return new FilterResult();
        }

        $result->setCondition(sprintf(
            "NOT (%s)",
            $conditions
        ));

        return $result;
    }
}