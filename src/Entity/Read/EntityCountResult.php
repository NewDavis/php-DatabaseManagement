<?php

namespace NewDavis\DatabaseManagement\Entity\Read;

use NewDavis\DatabaseManagement\Entity\Read\Criteria\Criteria;

class EntityCountResult
{
    public function __construct(
        private readonly int $total,
        private readonly Criteria $criteria,
        private readonly EntityReadStatementCollection $statements
    ) {
    }

    /**
     * @return int
     */
    public function getTotal(): int
    {
        return $this->total;
    }

    /**
     * @return Criteria
     */
    public function getCriteria(): Criteria
    {
        return $this->criteria;
    }

    /**
     * @return EntityReadStatementCollection
     */
    public function getStatements(): EntityReadStatementCollection
    {
        return $this->statements;
    }
}