<?php

namespace NewDavis\DatabaseManagement\Entity\Builder\Delete;

use NewDavis\DatabaseManagement\Entity\Builder\Condition\ConditionBuilder;
use NewDavis\DatabaseManagement\Entity\Read\Criteria\Criteria;
use NewDavis\DatabaseManagement\Entity\Write\EntityWriteStatement;
use NewDavis\DatabaseManagement\Entity\Write\EntityWriteStatementCollection;

class DeleteBuilder extends ConditionBuilder
{
    public function build(Criteria $criteria): EntityWriteStatementCollection
    {
        $joinMapping = $this->mapInternalNameWithJoinAlias($criteria);
        $joins = $this->buildJoins($joinMapping);
        $where = $this->buildWhere($criteria, $joinMapping);
        $sorting = $this->buildSorting($criteria);
        $limit = $this->buildLimit($criteria);
        $offset = $this->buildOffset($criteria);

        $query = <<<SQL
DELETE FROM `{$this->definition->getEntityName()}`
{$joins}
{$where->getQuery()} 
{$sorting} 
{$limit} {$offset}
SQL;

        return new EntityWriteStatementCollection([
            new EntityWriteStatement(
                $query,
                [
                    ...$where->getParameters()
                ]
            )
        ]);
    }
}