<?php

namespace NewDavis\DatabaseManagement\Entity\Builder\Read;

use NewDavis\DatabaseManagement\Entity\EntityDefinitionInterface;
use NewDavis\DatabaseManagement\Entity\EntityRegistry;
use NewDavis\DatabaseManagement\Entity\Field\Serializable;
use NewDavis\DatabaseManagement\Entity\Read\Criteria\Criteria;
use NewDavis\DatabaseManagement\Entity\Read\Criteria\Filter\FilterInterface;
use NewDavis\DatabaseManagement\Entity\Read\Criteria\Filter\MultiFilterInterface;
use NewDavis\DatabaseManagement\Entity\Read\Criteria\Filter\SearchableFilterInterface;

abstract class ReadBuilder
{
    public function __construct(
        protected readonly EntityRegistry $registry,
        protected readonly EntityDefinitionInterface $definition
    ) {
    }

    public function buildWhere(Criteria $criteria): string
    {
        $where = [];

        /** @var FilterInterface $filter */
        foreach ($criteria->getFilters() as $filter) {
            if ($filter instanceof SearchableFilterInterface) {
                $serializableField = $this->definition->getFields()->getByInternalName($filter->getInternalName());

                $value = $filter->getSearchValue();
                if ($serializableField instanceof Serializable) {
                    $value = $serializableField->getSerializer()->encode($value);
                }

                $property = "`{$this->definition->getEntityName()}`.`{$filter->getInternalName()}`";

                $where[] = $filter::build($value, $property);
            } else if ($filter instanceof MultiFilterInterface) {
                // TODO MultiFilter
            }
        }

        return implode(" AND\n", $where);
    }

    public function buildSorting(Criteria $criteria): string
    {
        return '';
    }

    public function buildLimit(Criteria $criteria): string
    {
        if ($criteria->getLimit() === Criteria::NO_LIMIT) return '';

        return "LIMIT {$criteria->getLimit()}";
    }

    public function buildOffset(Criteria $criteria): string
    {
        if ($criteria->getOffset() === Criteria::NO_OFFSET) return '';

        return "OFFSET {$criteria->getOffset()}";
    }
}