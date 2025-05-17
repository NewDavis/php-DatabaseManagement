<?php

namespace NewDavis\DatabaseManagement\Core\Schema;

use NewDavis\DatabaseManagement\Core\Driver\Statement;
use NewDavis\DatabaseManagement\Core\Entity\Entity;
use NewDavis\DatabaseManagement\Core\Entity\EntityCollection;
use NewDavis\DatabaseManagement\Core\Entity\Field\DateTimeField;
use NewDavis\DatabaseManagement\Core\Entity\Field\Field;
use NewDavis\DatabaseManagement\Core\Entity\Field\Relation\ManyToManyRelation;
use NewDavis\DatabaseManagement\Core\Entity\Field\Relation\RelationField;
use NewDavis\DatabaseManagement\Core\Search\Criteria\Criteria;

class SchemaBuilder
{

    public static function search($definition, Criteria $criteria) : Statement
    {
        $statement = new Statement();

        $buildWhereResult = self::buildWhere($definition, $criteria, $statement);
        $joins = $buildWhereResult['joins'];
        $where = $buildWhereResult['where'];
        $order = self::buildOrder($definition, $criteria);
        $limit = self::buildLimit($criteria);

        $statement->setStatement(
            rtrim(
                sprintf(
                    "SELECT `%s`.* FROM `%s` %s %s %s %s",
                    $definition::getEntityName(),
                    $definition::getEntityName(),
                    implode(' ', $joins),
                    $where ?? '',
                    $order ?? '',
                    $limit ?? ''
                )
            )
        );

        return $statement;
    }

    public static function searchIds($definition, Criteria $criteria) : Statement
    {
        $statement = new Statement();

        $buildWhereResult = self::buildWhere($definition, $criteria, $statement);
        $joins = $buildWhereResult['joins'];
        $where = $buildWhereResult['where'];
        $order = self::buildOrder($definition, $criteria);
        $limit = self::buildLimit($criteria);

        $statement->setStatement(
            rtrim(
                sprintf(
                    "SELECT id FROM `%s` %s %s %s %s",
                    $definition::getEntityName(),
                    implode(' ', $joins),
                    $where ?? '',
                    $order ?? '',
                    $limit ?? ''
                )
            )
        );

        return $statement;
    }

    public static function count($definition, Criteria $criteria) : Statement
    {
        $statement = new Statement();

        $buildWhereResult = self::buildWhere($definition, $criteria, $statement);
        $joins = $buildWhereResult['joins'];
        $where = $buildWhereResult['where'];

        $statement->setStatement(
            rtrim(
                sprintf(
                    "SELECT COUNT(id) FROM `%s` %s %s",
                    $definition::getEntityName(),
                    implode(' ', $joins),
                    $where ?? ''
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
                    implode(', ', array_map(
                        fn($property) => sprintf(
                            '`%s`.%s',
                            $definition::getEntityName(),
                            $property
                        ),
                        array_values($properties)
                    )),
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

    public static function update($definition, EntityCollection $entities, ?array $changedProperties): array
    {
        $statements = [];

        foreach ($entities->getEntities() as $index => $entity) {
            $statement = new Statement();

            $properties = self::getProperties(
                $definition,
                $entity,
                [...array_keys($changedProperties[$index]), 'updatedAt']
            );

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

    public static function selectExistingManyToManyDatasets(
        $definition,
        ManyToManyRelation $field,
        string $id
    ): Statement {
        $statement = new Statement();

        $tableName = TableSchemaBuilder::createManyToManyTableName(
            $definition,
            $field->getRelatedToDefinition()
        );

        $statement->setStatement(
            sprintf(
                "SELECT %s FROM `%s` WHERE `%s` = ?",
                $field->getRelatedToDefinition()::getEntityName() . '_id',
                $tableName,
                $definition::getEntityName() . '_id',
            )
        );
        $statement->addParameter('?', $id);

        return $statement;
    }

    public static function deleteDeletedManyToManyDatasets(
        $definition,
        ManyToManyRelation $field,
        string $id,
        array $toBeDeleted
    ): Statement {
        $statement = new Statement();

        $tableName = TableSchemaBuilder::createManyToManyTableName(
            $definition,
            $field->getRelatedToDefinition()
        );

        $statement->addParameter('?', $id);
        $statement->setStatement(
            sprintf(
                "DELETE FROM `%s` WHERE `%s` = ? AND `%s` IN (%s)",
                $tableName,
                $definition::getEntityName() . '_id',
                $field->getRelatedToDefinition()::getEntityName() . '_id',
                implode(
                    ', ',
                    array_map(
                        function ($s) use ($statement) {
                            $statement->addParameter('?', $s);
                            return '?';
                        },
                        $toBeDeleted
                    )
                )
            )
        );

        return $statement;
    }

    public static function writeManyToManyDatasets(
        $definition,
        ManyToManyRelation $field,
        string $id,
        array $relatedIds
    ): Statement {
        $statement = new Statement();

        $tableName = TableSchemaBuilder::createManyToManyTableName(
            $definition,
            $field->getRelatedToDefinition()
        );

        $valuesSQL = "";
        foreach ($relatedIds as $relatedId) {
            $valuesSQL .= sprintf(
                "('%s', ?), ",
                $id
            );

            $statement->addParameter('?', $relatedId);
        }

        $statement->setStatement(
            sprintf(
                "INSERT INTO `%s` VALUES %s;",
                $tableName,
                rtrim($valuesSQL, ', ')
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

    /**
     * @param $definition
     * @param array $storageName
     * @return array<Field>
     */
    public static function filterFieldsByStorageName($definition, ...$storageName) : array
    {
        $filtered = [];

        foreach ($definition::getDefinitionFields() as $field) {
            if($field->getStorageName() == null && in_array($field->getRelatedBy(), $storageName)) {
                $filtered[] = $field;
                break;
            }else if (in_array($field->getStorageName(), $storageName)) {
                $filtered[] = $field;
                break;
            }
        }

        return $filtered;
    }

    /**
     * @param $definition
     * @param Criteria $criteria
     * @param Statement|null $statement
     * @return array{joins: array, where: string}
     */
    private static function buildWhere($definition, Criteria $criteria, ?Statement $statement): array
    {
        $joins = [];
        $where = null;
        foreach ($criteria->getFilter() as $filter) {
            $converted = $filter->convert($definition);

            if($where == null && $converted->getCondition()) {
                $where = 'WHERE ';
            }

            if(!in_array($converted->getCondition(), $joins)) {
                $joins = array_merge($joins, $converted->getJoins());
            }

            $where .= $converted->getCondition() . ' AND ';
            foreach ($converted->getParameters() as $key => $value) {
                $statement?->addParameter($key, $value);
            }
        }

        if($where) {
            $where = rtrim($where, ' AND ');
        }

        return [
            'joins' => $joins,
            'where' => $where
        ];
    }

    /**
     * @param $definition
     * @param Criteria $criteria
     * @return string|null
     */
    private static function buildOrder($definition, Criteria $criteria): ?string
    {
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

        if($orderSQL) {
            $orderSQL = sprintf(
                $orderSQL,
                rtrim($orderBys, ', ')
            );
        }

        return $orderSQL;
    }

    /**
     * @param Criteria $criteria
     * @return string|null
     */
    private static function buildLimit(Criteria $criteria): ?string
    {
        $limitSQL = null;
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

        return $limitSQL;
    }

    private static function getProperties($definition, Entity $entity, ?array $changedProperties = null) : array
    {
        $properties = [];

        /** @var Field $field */
        foreach ($definition::getDefinitionFields() as $field) {
            if($field instanceof RelationField) continue;

            if($changedProperties && !in_array($field->getInternalName(), $changedProperties)) continue;
            if($changedProperties && $field->getInternalName() == 'id') continue;

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

            $value = self::convertValue($property->getValue($entity));

            $values[] = sprintf(
                "%s",
                $value
            );
        }

        return $values;
    }

    private static function convertValue(mixed $value)
    {
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
            case 'array':
                $json = json_encode($value);
                if(json_last_error() == JSON_ERROR_NONE) {
                    $value = $json;
                }
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

        return $value;
    }

}