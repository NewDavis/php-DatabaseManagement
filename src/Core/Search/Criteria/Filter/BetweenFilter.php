<?php

namespace NewDavis\DatabaseManagement\Core\Search\Criteria\Filter;

use NewDavis\DatabaseManagement\Core\Schema\SchemaBuilder;
use NewDavis\DatabaseManagement\Core\Search\Criteria\Filter\Exception\UnknownInternalNameException;

class BetweenFilter implements Filter
{
    /**
     * @param string $internalName
     * @param int $start,
     * @param int $end,
     */
    public function __construct(
        private readonly string $internalName,
        private readonly int $start,
        private readonly int $end,
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
     * @return int
     */
    public function getStart(): int
    {
        return $this->start;
    }

    /**
     * @return int
     */
    public function getEnd(): int
    {
        return $this->end;
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
        if(count($fields) == 0) {
            throw new UnknownInternalNameException($this->getInternalName());
        }

        $field = $fields[0];
        $result = new FilterResult();

        $result->addParameter('?', sprintf(
                "%s",
                $this->getStart()
            ));

        $result->addParameter('?', sprintf(
                "%s",
                $this->getEnd()
            ));

        $result->setCondition(sprintf(
            "`%s`.`%s` BETWEEN ? AND ?",
            $definition::getEntityName(),
            $field->getStorageName(),
        ));

        return $result;
    }
}