<?php

namespace NewDavis\DatabaseManagement\Entity\Builder\Read\Count;

use NewDavis\DatabaseManagement\Entity\Builder\Condition\ConditionBuilder;
use NewDavis\DatabaseManagement\Entity\Read\Criteria\Criteria;
use NewDavis\DatabaseManagement\Entity\Read\EntityReadStatement;
use NewDavis\DatabaseManagement\Entity\Read\EntityReadStatementCollection;

class CountBuilder extends ConditionBuilder
{
    public function build(Criteria $criteria): EntityReadStatementCollection
    {
        $joinMapping = $this->mapInternalNameWithJoinAlias($criteria);
        $joins = $this->buildJoins($joinMapping);
        $where = $this->buildWhere($criteria, $joinMapping);
        $limit = $this->buildLimit($criteria);
        $offset = $this->buildOffset($criteria);

        $query = <<<SQL
SELECT COUNT(`{$this->definition->getEntityName()}`.`id`) AS `count` FROM `{$this->definition->getEntityName()}`
{$joins}
{$where->getQuery()}  
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