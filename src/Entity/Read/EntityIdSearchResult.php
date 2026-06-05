<?php

namespace NewDavis\DatabaseManagement\Entity\Read;

use NewDavis\DatabaseManagement\Entity\EntityIdCollection;
use NewDavis\DatabaseManagement\Entity\Read\Criteria\Criteria;

class EntityIdSearchResult
{
    public function __construct(
        private readonly EntityIdCollection $ids,
        private readonly Criteria $criteria,
        private readonly EntityReadStatementCollection $statements
    ) {
    }

    /**
     * @return EntityIdCollection
     */
    public function getIds(): EntityIdCollection
    {
        return $this->ids;
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