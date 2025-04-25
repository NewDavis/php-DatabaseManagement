<?php

namespace NewDavis\DatabaseManagement\Core\Schema;

use NewDavis\DatabaseManagement\Core\Driver\Statement;
use NewDavis\DatabaseManagement\Core\Entity\Entity;
use NewDavis\DatabaseManagement\Core\Entity\EntityCollection;
use NewDavis\DatabaseManagement\Core\Entity\Field\DateTimeField;
use NewDavis\DatabaseManagement\Core\Entity\Field\Field;
use NewDavis\DatabaseManagement\Core\Search\Criteria\Criteria;

class SchemaBuilder
{

    public static function search($definition, Criteria $criteria) : Statement
    {
        $statement = new Statement();

        $where = null;
        foreach ($criteria->getFilter() as $filter) {
            $converted = $filter->convert($definition);

            if($where == null && $converted->getCondition()) {
                $where = 'WHERE ';
            }

            $where .= $converted->getCondition() . ' AND ';
            foreach ($converted->getParameters() as $key => $value) {
                $statement->addParameter($key, $value);
            }
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

        $statement->setStatement(
            rtrim(
                sprintf(
                    "SELECT * FROM `%s` %s %s %s",
                    $definition::getEntityName(),
                    $where ?? '',
                    $orderSQL ?? '',
                    $limitSQL
                )
            )
        );

        return $statement;
    }

    public static function searchIds($definition, Criteria $criteria) : Statement
    {
        $statement = new Statement();

        $where = null;
        foreach ($criteria->getFilter() as $filter) {
            $converted = $filter->convert($definition);

            if($where == null && $converted->getCondition()) {
                $where = 'WHERE ';
            }

            $where .= $converted->getCondition() . ' AND ';
            foreach ($converted->getParameters() as $key => $value) {
                $statement->addParameter($key, $value);
            }
        }
        $where = rtrim($where, ' AND ');

        $orderSQL = null;
        $orderBys = '';
        foreach ($criteria->getSorting() as $sorting) {
            if(!$orderSQL) {
                $orderSQL = 'ORDER BY %s';
            }

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

        $statement->setStatement(
            rtrim(
                sprintf(
                    "SELECT id FROM `%s` %s %s %s",
                    $definition::getEntityName(),
                    $where ?? '',
                    $orderSQL ?? '',
                    $limitSQL
                )
            )
        );

        return $statement;
    }

    public static function create($definition, EntityCollection $entities) : array
    {
        $statements = [];

        foreach ($entities->getEntities() as $entity) {
            $statement = new Statement();

            $properties = self::getProperties($definition, $entity);

            $values = self::getValues($properties, $entity);

            $statement->setStatement(
                sprintf(
                    "INSERT INTO `%s` (%s) VALUES (%s);",
                    $definition::getEntityName(),
                    implode(', ', array_values($properties)),
                    implode(', ', array_map(
                        function ($value) use ($statement) {
                            $statement->addParameter('?', $value);

                            return '?';
                        },
                        $values
                    ))
                )
            );

            $statements[] = $statement;
        }

        return $statements;
    }

    public static function update($definition, EntityCollection $entities): array
    {
        $statements = [];

        foreach ($entities->getEntities() as $entity) {
            $statement = new Statement();

            $properties = self::getProperties($definition, $entity);

            $values = self::getValues($properties, $entity);

            $statement->setStatement(
                sprintf(
                    "UPDATE `%s` SET %s WHERE `%s`.`%s` = ?;",
                    $definition::getEntityName(),
                    implode(', ', array_map(
                        function ($property, $value) use ($definition, $statement) {
                            $statement->addParameter('?', $value);

                            return sprintf(
                                '`%s`.%s = ?',
                                $definition::getEntityName(),
                                $property
                            );
                        },
                        $properties,
                        $values
                    )),
                    $definition::getEntityName(),
                    'id'
                )
            );

            $statement->addParameter('?', $entity->getId());

            $statements[] = $statement;
        }

        return $statements;
    }

    private static function getProperties($definition, Entity $entity) : array
    {
        $properties = [];

        /** @var Field $field */
        foreach ($definition::getDefinitionFields() as $field) {
            $reflectionClass = new \ReflectionClass($entity);
            if(!$reflectionClass->hasProperty($field->getInternalName())) continue;

            $property = $reflectionClass->getProperty($field->getInternalName());
            if(!$property->isInitialized($entity)) continue;

            $properties[$field->getInternalName()] = sprintf(
                "`%s`",
                $field->getStorageName()
            );
        }

        return $properties;
    }

    private static function getValues($properties, Entity $entity) : array
    {
        $values = [];

        foreach ($properties as $internalName => $storageName) {
            $reflectionClass = new \ReflectionClass($entity);
            if(!$reflectionClass->hasProperty($internalName)) continue;

            $property = $reflectionClass->getProperty($internalName);
            if(!$property->isInitialized($entity)) continue;

            $value = $property->getValue($entity);
            switch (gettype($value)) {
                case 'string':
                    $value = (string)$value;
                    break;
                case 'integer':
                    $value = (int)$value;
                    break;
                case 'boolean':
                    $value = $value ? 1 : 0;
                    break;
                case 'object':
                    if($value instanceof \DateTimeImmutable) {
                        $value = $value->format(DateTimeField::FORMAT);
                    }
                    break;
                default:
                    dd(gettype($value));
                    break;
            }

            $values[] = sprintf(
                "%s",
                $value
            );
        }

        return $values;
    }

    public static function delete($definition, Criteria $criteria) : Statement
    {
        $statement = new Statement();

        $where = null;
        foreach ($criteria->getFilter() as $filter) {
            $converted = $filter->convert($definition);

            if($where == null && $converted->getCondition()) {
                $where = 'WHERE ';
            }

            $where .= $converted->getCondition() . ' AND ';
            foreach ($converted->getParameters() as $key => $value) {
                $statement->addParameter($key, $value);
            }
        }
        $where = rtrim($where, ' AND ');

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

        $statement->setStatement(
            rtrim(
                sprintf(
                    "DELETE FROM `%s` %s %s",
                    $definition::getEntityName(),
                    $where ?? '',
                    $limitSQL
                )
            )
        );

        return $statement;
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