<?php

namespace NewDavis\DatabaseManagement\Entity\Builder\Read\Id;

use NewDavis\DatabaseManagement\Entity\Builder\Read\ReadBuilder;
use NewDavis\DatabaseManagement\Entity\Read\Criteria\Criteria;
use NewDavis\DatabaseManagement\Entity\Read\EntityReadStatement;
use NewDavis\DatabaseManagement\Entity\Read\EntityReadStatementCollection;

class ReadIdBuilder extends ReadBuilder
{
    public function build(Criteria $criteria): EntityReadStatementCollection
    {
        $where = $this->buildWhere($criteria);
        $sorting = $this->buildSorting($criteria);
        $limit = $this->buildLimit($criteria);
        $offset = $this->buildOffset($criteria);

        $query = <<<SQL
SELECT id FROM `{$this->definition->getEntityName()}`
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