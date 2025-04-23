<?php

namespace NewDavis\DatabaseManagement\Core\Schema;

use NewDavis\DatabaseManagement\Core\Entity\Field\Field;
use NewDavis\DatabaseManagement\Core\Search\Criteria\Criteria;

class SchemaBuilder
{

    public static function search($definition, Criteria $criteria)
    {
        $where = null;
        foreach ($criteria->getFilter() as $filter) {
            $converted = $filter->convert($definition);

            if($where == null && $converted) {
                $where = 'WHERE ';
            }

            $where .= $converted . ' AND ';
        }
        $where = rtrim($where, ' AND ');

        $orderSQL = 'ORDER BY %s';
        $orderBys = '';
        foreach ($criteria->getSorting() as $sorting) {
            $orderBys .= sprintf(
                "`%s`.`%s` %s, ",
                $definition::getEntityName(),
                $sorting->getBy($definition),
                $sorting->getDirection()
            );
        }
        $orderSQL = sprintf(
            $orderSQL,
            rtrim($orderBys, ', ')
        );

        $limitSQL = '';
        $limit = $criteria->getLimit();
        if($limit != -1) {
            $page = $criteria->getPage();
            $offset = $criteria->getOffset() == -1 ? (($page - 1) * $limit) : $criteria->getOffset();

            $limitSQL = sprintf(
                "LIMIT %d OFFSET %d",
                $limit,
                $offset
            );
        }

        return rtrim(sprintf(
            "SELECT * FROM `%s` %s %s %s",
            $definition::getEntityName(),
            $where ?? '',
            $orderSQL ?? '',
            $limitSQL
        ));
    }

    /**
     * @param $definition
     * @param array $internalNames
     * @return array<Field>
     */
    public static function filterFieldsByInternalName($definition, ...$internalNames) : array
    {
        $filtered = [];

        foreach ($definition::getDefinitionFields() as $field) {
            if (in_array($field->getInternalName(), $internalNames)) {
                $filtered[] = $field;
                break;
            }
        }

        return $filtered;
    }

}