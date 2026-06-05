<?php

namespace NewDavis\DatabaseManagement\Entity\Read;

use NewDavis\DatabaseManagement\Entity\AbstractEntityCollection;
use NewDavis\DatabaseManagement\Entity\Read\Criteria\Criteria;

class EntitySearchResult
{
    public function __construct(
        private readonly AbstractEntityCollection $entities,
        private readonly Criteria $criteria,
        private readonly EntityReadStatementCollection $statements
    ) {
    }

    /**
     * @return AbstractEntityCollection
     */
    public function getEntities(): AbstractEntityCollection
    {
        return $this->entities;
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