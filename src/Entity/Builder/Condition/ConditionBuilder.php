<?php

namespace NewDavis\DatabaseManagement\Entity\Builder\Condition;

use NewDavis\DatabaseManagement\Entity\EntityDefinitionInterface;
use NewDavis\DatabaseManagement\Entity\EntityRegistry;
use NewDavis\DatabaseManagement\Entity\Exception\Search\UnableToFindMatchingRelationalFieldByInternalName;
use NewDavis\DatabaseManagement\Entity\Field\Relational\ManyToManyRelation;
use NewDavis\DatabaseManagement\Entity\Field\Relational\ManyToOneRelation;
use NewDavis\DatabaseManagement\Entity\Field\Relational\OneToManyRelation;
use NewDavis\DatabaseManagement\Entity\Field\Relational\OneToOneRelation;
use NewDavis\DatabaseManagement\Entity\Field\Relational\RelatedByInterface;
use NewDavis\DatabaseManagement\Entity\Field\Relational\RelationalField;
use NewDavis\DatabaseManagement\Entity\Field\Serializable;
use NewDavis\DatabaseManagement\Entity\Field\StorableInterface;
use NewDavis\DatabaseManagement\Entity\Read\Criteria\Criteria;
use NewDavis\DatabaseManagement\Entity\Read\Criteria\Filter\FilterInterface;
use NewDavis\DatabaseManagement\Entity\Read\Criteria\Filter\FilterResult;
use NewDavis\DatabaseManagement\Entity\Read\Criteria\Filter\MultiFilterInterface;
use NewDavis\DatabaseManagement\Entity\Read\Criteria\Filter\SearchableFilterInterface;
use NewDavis\DatabaseManagement\Entity\Read\Criteria\Sort\FieldSorting;
use NewDavis\DatabaseManagement\Entity\Read\Criteria\Sort\SortingInterface;
use NewDavis\DatabaseManagement\Util\Helper\EntityHelper;
use NewDavis\DatabaseManagement\Util\Helper\EntityTableHelper;
use function Adminer\dump_csv;

abstract class ConditionBuilder
{
    private const MAPPING_JOIN_ALIAS = '_mapping';

    public function __construct(
        protected readonly EntityRegistry $registry,
        protected readonly EntityDefinitionInterface $definition
    ) {
    }

    public function mapInternalNameWithJoinAlias(Criteria $criteria): array
    {
        $mapping = [];

        /** @var SearchableFilterInterface $filter */

        $searchableFilters = [];
        foreach ($criteria->getFilters() as $filter) {
            if ($filter instanceof MultiFilterInterface) {
                foreach ($filter->getFilters() as $subFilter) {
                    $searchableFilters[] = $subFilter;
                }
                continue;
            }

            $searchableFilters[] = $filter;
        }

        foreach ($searchableFilters as $filter) {
            $path = '';

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
                if (!(
                    $relationalField instanceof ManyToManyRelation ||
                    $relationalField instanceof ManyToOneRelation ||
                    $relationalField instanceof OneToOneRelation ||
                    $relationalField instanceof OneToManyRelation
                )) {
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

    public function buildJoins(array $joinMapping): string
    {
        $convertedJoins = [];

        /**
         * @var string $searchName
         * @var JoinCollection $joins
         */
        foreach ($joinMapping as $joins) {
            /** @var string|null $lastAlias */
            $lastAlias = null;

            /** @var Join $join */
            foreach ($joins as $join) {
                if ($lastAlias == null) {
                    $lastAlias = $join->getDefinition()->getEntityName();
                }

                /** @var StorableInterface $relatedDefinitionRelatedField */
                $relatedDefinitionRelatedField = $join->getRelatedDefinition()->getFields()->getByInternalName(
                    $join->getRelationalField()->getRelatedToInternalName()
                );

                if ($join->getRelationalField() instanceof ManyToManyRelation) {
                    /** @var StorableInterface $relatedByField */
                    $relatedByField = $join->getDefinition()->getFields()->getByInternalName(
                        $join->getRelationalField()->getRelatedByInternalName()
                    );

                    $mappingTableName = EntityTableHelper::buildMappingTableName(
                        $join->getDefinition(),
                        $this->registry,
                        $join->getRelationalField()
                    );
                    $mappingJoinAlias = self::MAPPING_JOIN_ALIAS;

                    // Mapping Join
                    $convertedJoins[$join->getAlias() . self::MAPPING_JOIN_ALIAS] = <<<SQL
JOIN `{$mappingTableName}` {$join->getAlias()}{$mappingJoinAlias}
    ON `{$lastAlias}`.`{$relatedDefinitionRelatedField->getStorageName()}` = `{$join->getAlias()}{$mappingJoinAlias}`.`{$join->getDefinition()->getEntityName()}_{$relatedByField->getStorageName()}`
SQL;

                    // Join from Mapping to Related Table
                    $convertedJoins[$join->getAlias()] = <<<SQL
JOIN `{$join->getRelatedDefinition()->getEntityName()}` {$join->getAlias()}
    ON `{$join->getAlias()}{$mappingJoinAlias}`.`{$join->getRelatedDefinition()->getEntityName()}_{$relatedDefinitionRelatedField->getStorageName()}` = `{$join->getAlias()}`.`{$relatedDefinitionRelatedField->getStorageName()}`
SQL;

                    $lastAlias = $join->getAlias();

                    continue;
                }

                $convertedJoins[$join->getAlias()] = <<<SQL
JOIN `{$join->getRelatedDefinition()->getEntityName()}` {$join->getAlias()}
    ON `{$lastAlias}`.`{$join->getRelationalField()->getStorageName()}` = `{$join->getAlias()}`.`{$relatedDefinitionRelatedField->getStorageName()}`
SQL;

                $lastAlias = $join->getAlias();
            }
        }

        return implode("\n", $convertedJoins);
    }

    private function convertSearchableFilter(SearchableFilterInterface $filter, array $joinMapping): FilterResult
    {
        if (array_key_exists($filter->getInternalName(), $joinMapping)) {
            /** @var JoinCollection $joins */
            $joins = $joinMapping[$filter->getInternalName()];
            $explodedInternalName = explode('.', $filter->getInternalName());
            $lastProperty = array_pop($explodedInternalName);

            $tableName = $joins->last()->getAlias();
            $serializableField = $joins->last()->getRelatedDefinition()->getFields()->getByInternalName(
                $lastProperty
            );
        } else {
            $tableName = $this->definition->getEntityName();
            $serializableField = $this->definition->getFields()->getByInternalName($filter->getInternalName());
        }

        $field = $serializableField->getStorageName();

        $value = $filter->getSearchValue();

        if ($serializableField instanceof Serializable) {
            $value = $serializableField->getSerializer()->encode($value);
        }

        $property = "`{$tableName}`.`{$field}`";

        return $filter::build($value, $property);
    }

    public function buildWhere(Criteria $criteria, array $joinMapping): FilterResult
    {
        $queries = [];
        $parameters = [];

        /** @var FilterInterface $filter */
        foreach ($criteria->getFilters() as $filter) {
            if (
                $filter instanceof SearchableFilterInterface &&
                is_array($filter->getSearchValue()) &&
                count($filter->getSearchValue()) == 0
            ) continue;

            if ($filter instanceof MultiFilterInterface) {
                $subQueries = [];
                $subParameters = [];

                foreach ($filter->getFilters() as $subFilter) {
                    if (
                        $subFilter instanceof SearchableFilterInterface &&
                        is_array($subFilter->getSearchValue()) &&
                        count($subFilter->getSearchValue()) == 0
                    ) continue;

                    $subFilterResult = $this->convertSearchableFilter($subFilter, $joinMapping);

                    $subQueries[] = $subFilterResult->getQuery();
                    foreach ($subFilterResult->getParameters() as $parameter) {
                        $subParameters[] = $parameter;
                    }
                }

                $filterResult = $filter::build(
                    implode(" " . $filter->getType()->value . "\n", $subQueries),
                    $subParameters
                );
            } else {
                $filterResult = $this->convertSearchableFilter($filter, $joinMapping);
            }

            $queries[] = $filterResult->getQuery();

            foreach ($filterResult->getParameters() as $parameter) {
                $parameters[] = $parameter;
            }
        }

        return new FilterResult(
            (count($queries) > 0 ? " WHERE\n" : '') . implode(" AND\n", $queries),
            $parameters
        );
    }

    public function buildSorting(Criteria $criteria): string
    {
        $fieldSorting = [];

        /** @var SortingInterface $sorting */
        foreach ($criteria->getSortingCollection() as $sorting) {
            if ($sorting instanceof FieldSorting) {
                $field = null;
                try {
                    $field = $this->definition->getFields()->getByInternalName($sorting->getProperty());
                } catch (\Throwable $e) {
                    $field = $this->definition->getFields()->getByStorageName($sorting->getProperty());
                }

                if (!($field instanceof StorableInterface)) continue;

                $fieldSorting[] = sprintf(
                    '`%s` %s',
                    $field->getStorageName(),
                    $sorting->getDirection()->name
                );
            }
        }

        if (count($fieldSorting) > 0) {
            return 'ORDER BY ' . implode(", ", $fieldSorting);
        }

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