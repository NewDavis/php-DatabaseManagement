<?php

namespace NewDavis\DatabaseManagement\Entity\Builder\Read\Entity;

use NewDavis\DatabaseManagement\Entity\Builder\Condition\ConditionBuilder;
use NewDavis\DatabaseManagement\Entity\Read\Criteria\Criteria;
use NewDavis\DatabaseManagement\Entity\Read\EntityReadStatement;
use NewDavis\DatabaseManagement\Entity\Read\EntityReadStatementCollection;

class ReadEntityBuilder extends ConditionBuilder
{
    public function build(Criteria $criteria): EntityReadStatementCollection
    {
        $joinMapping = $this->mapInternalNameWithJoinAlias($criteria);
        $joins = $this->buildJoins($joinMapping);
        $where = $this->buildWhere($criteria, $joinMapping);
        $sorting = $this->buildSorting($criteria);
        $limit = $this->buildLimit($criteria);
        $offset = $this->buildOffset($criteria);

        $query = <<<SQL
SELECT `{$this->definition->getEntityName()}`.* FROM `{$this->definition->getEntityName()}`
{$joins}
WHERE {$where->getQuery()} 
{$sorting} 
{$limit} {$offset}
SQL;

        return new EntityReadStatementCollection([
            new EntityReadStatement(
                $query,
                [
                    ...$where->getParameters()
                ]
            )
        ]);
    }
}