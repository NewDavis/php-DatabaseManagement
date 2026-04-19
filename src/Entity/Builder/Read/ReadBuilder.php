<?php

namespace NewDavis\DatabaseManagement\Entity\Builder\Read;

use NewDavis\DatabaseManagement\Entity\EntityDefinitionInterface;
use NewDavis\DatabaseManagement\Entity\EntityRegistry;
use NewDavis\DatabaseManagement\Entity\Exception\Search\UnableToFindMatchingRelationalFieldByInternalName;
use NewDavis\DatabaseManagement\Entity\Field\Relational\RelationalField;
use NewDavis\DatabaseManagement\Entity\Field\Serializable;
use NewDavis\DatabaseManagement\Entity\Read\Criteria\Criteria;
use NewDavis\DatabaseManagement\Entity\Read\Criteria\Filter\FilterInterface;
use NewDavis\DatabaseManagement\Entity\Read\Criteria\Filter\FilterResult;
use NewDavis\DatabaseManagement\Entity\Read\Criteria\Filter\MultiFilterInterface;
use NewDavis\DatabaseManagement\Entity\Read\Criteria\Filter\SearchableFilterInterface;

abstract class ReadBuilder
{
    public function __construct(
        protected readonly EntityRegistry $registry,
        protected readonly EntityDefinitionInterface $definition
    ) {
    }

    public function mapInternalNameWithJoinAlias(Criteria $criteria): array
    {
        $mapping = [];

        /** @var SearchableFilterInterface $filter */

        foreach ($criteria->getFilters()->filter(SearchableFilterInterface::class) as $filter) {
            $path = '';

            /** @var RelationalField|null $currentRelationalField */
            $currentRelationalField = null;
            /** @var EntityDefinitionInterface|null $previousDefinition */
            $currentDefinition = $this->definition;

            $level = explode('.', $filter->getInternalName());

            if (count($level) <= 1) {
                continue;
            }

            $joinCollection = new JoinCollection([]);

            $currentLevel = 0;
            foreach ($level as $part) {
                $currentLevel++;
                if ($currentLevel == count($level)) {
                    continue;
                }

                $path .= ($path != '' ? '_' : '') . $part;

                if (array_key_exists($path, $mapping)) {
                    continue;
                }

                $relationalField = $currentDefinition->getFields()->getByInternalName($part);
                if (!$relationalField instanceof RelationalField) {
                    throw new UnableToFindMatchingRelationalFieldByInternalName($part);
                }

                $relatedDefinition = $this->registry->getDefinitionByDefinitionClass(
                    $relationalField->getRelatedToDefinition()
                );

                $joinCollection->add(
                    new Join(
                        $currentDefinition,
                        $relationalField,
                        $relatedDefinition,
                        $path
                    )
                );

                $currentDefinition = $relatedDefinition;
            }

            $mapping[$filter->getInternalName()] = $joinCollection;
        }

        return $mapping;
    }

    public function buildWhere(Criteria $criteria, array $joinAliasMapping): FilterResult
    {
        $queries = [];
        $parameters = [];

        /** @var FilterInterface $filter */
        foreach ($criteria->getFilters() as $filter) {
            if ($filter instanceof SearchableFilterInterface) {
                $serializableField = $this->definition->getFields()->getByInternalName($filter->getInternalName());

                $value = $filter->getSearchValue();
                if ($serializableField instanceof Serializable) {
                    $value = $serializableField->getSerializer()->encode($value);
                }

                $property = "`{$this->definition->getEntityName()}`.`{$filter->getInternalName()}`";

                $filterResult = $filter::build($value, $property);

                $queries[] = $filterResult->getQuery();
                foreach ($filterResult->getParameters() as $parameter) {
                    $parameters[] = $parameter;
                }
            } else if ($filter instanceof MultiFilterInterface) {
                // TODO MultiFilter
            }
        }

        return new FilterResult(
            implode(" AND\n", $queries),
            $parameters
        );
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