<?php

namespace NewDavis\DatabaseManagement\Schema;

use App\Entity\Account\AccountDefinition;
use DateTimeImmutable;
use NewDavis\DatabaseManagement\Core\Criteria\Criteria;
use NewDavis\DatabaseManagement\Core\Criteria\Filter\EqualsAnyFilter;
use NewDavis\DatabaseManagement\Core\Criteria\Filter\EqualsFilter;
use NewDavis\DatabaseManagement\Core\Entity\Entity;
use NewDavis\DatabaseManagement\Core\Entity\EntityRepository;
use NewDavis\DatabaseManagement\Core\Entity\Property\Property;
use NewDavis\DatabaseManagement\Core\Entity\Property\Relation\ManyToManyProperty;
use NewDavis\DatabaseManagement\Core\Entity\Property\Relation\ManyToOneProperty;
use NewDavis\DatabaseManagement\Core\Entity\Property\Relation\OneToManyProperty;
use NewDavis\DatabaseManagement\Core\Entity\Property\Relation\OneToOneProperty;
use NewDavis\DatabaseManagement\Core\Entity\Property\Relation\RelationProperty;
use ReflectionClass;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;

class EntitySchemaBuilder extends SchemaBuilder
{

    public const DATE_TIME_FORMAT = 'Y-m-d H:i:s.u';

    private static int $maximumNestingDepth = 10;

    private static array $entitySchemaBuilders = [];

    private static array $repositories = [];

    private ReflectionClass|null $reflectionClass = null;


    private function __construct(private readonly EntityRepository   $repository,
                                 private readonly ?Entity            $entity,
                                 private readonly ContainerInterface $container)
    {
        parent::__construct($this->repository->getEntityDefinition());
    }

    /**
     * @return array
     *
     * returns an array in that format:
     * [query string, parameters array]
     */
    public function create() : array
    {
        $queries = [];

        // build query
        // INSERT INTO `table` VALUES ()

        $table = $this->getTable();
        $properties = $this->getProperties();
        $propertyValues = $this->getPropertiesWithValues($this->entity);

        $queries[$table] = [
            'query' => 'INSERT INTO `' . $table . '` (',
        ];

        $databaseProperties = '';
        $databaseParameters = '';

        foreach ($properties as $property) {
            if(!(array_key_exists($property->getProperty(), $propertyValues))) continue;

            $value = $propertyValues[$property->getProperty()];

            if(!isset($value)) continue;

            $databaseProperties .= $this->convertToDatabaseProperty($property) . ', ';
            $databaseParameters .= ':' . $this->convertToDatabaseProperty($property) . ', ';

            if($value instanceof Entity) {
                $value = $value->getId();
            }else if($value instanceof DateTimeImmutable) {
                $value = $value->format(self::DATE_TIME_FORMAT);
            }

            $queries[$table]['parameters'][':' . $this->convertToDatabaseProperty($property)] = $value;
        }

        $queries[$table]['query'] = $queries[$table]['query'] . rtrim($databaseProperties, ', ') . ') VALUES (' . rtrim($databaseParameters, ', ') . ')';

        // build query for manyToMany
        $queries = array_merge($queries, $this->setManyToManyProperties($propertyValues));

        return $queries;
    }

    /**
     * @return array
     *
     * returns an array in that format:
     * [query string, parameters array]
     */
    public function update() : array
    {
        $queries = [];

        // build query
        // UPDATE `{table}` SET property=? WHERE id=?

        $table = $this->getTable();
        $properties = $this->getProperties();
        $propertyValues = $this->getPropertiesWithValues($this->entity);

        $queries[$table] = [
            'query' => 'UPDATE `' . $table . '` SET ',
        ];

        foreach ($properties as $property) {
            if(!(array_key_exists($property->getProperty(), $propertyValues))) continue;

            $value = $propertyValues[$property->getProperty()];

            if(!isset($value)) continue;

            if($value instanceof Entity) {
                $value = $value->getId();
            }else if($value instanceof DateTimeImmutable) {
                $value = $value->format(self::DATE_TIME_FORMAT);
            }

            $queries[$table]['query'] .= $this->convertToDatabaseProperty($property) . ' = :' . $this->convertToDatabaseProperty($property) . ', ';
            $queries[$table]['parameters'][':' . $this->convertToDatabaseProperty($property)] = $value;
        }

        $queries[$table]['query'] = rtrim($queries[$table]['query'], ', ') . ' WHERE id = :id_main';
        $queries[$table]['parameters'][':id_main'] = $this->entity->getId();

        // build query for manyToMany
        $queries = array_merge($queries, $this->setManyToManyProperties($propertyValues, true));

        return $queries;
    }

    public function delete() : string
    {
        return 'DELETE FROM `' . $this->repository->getEntityDefinition()->getEntityName() . '` WHERE id=' . $this->entity->getId() . ';';
    }

    public function setManyToManyProperties(array $propertyValues, bool $reset = false)
    {
        $queries = [];

        $manyToManyProperties = $this->getManyToManyProperties();

        foreach ($manyToManyProperties as $property) {
            $entityId = $this->entity->getId();
            $referencedEntities = $propertyValues[$property->getProperty()];

            if($referencedEntities->count() == 0) continue;

            $databaseProperty = $this->convertToDatabaseProperty($this->repository->getEntityDefinition()->getEntityName());
            $referencedDatabaseProperty = $this->convertToDatabaseProperty($property->getReferencedEntity());

            $table = $this->getManyToManyTables([$property])[0];

            if($reset) {
                $queries[$table . '_reset'] = [
                    'query' => 'DELETE FROM `' . $table . '` WHERE ' . $databaseProperty . ' = :' . $databaseProperty,
                    'parameters' => [
                        ':' . $databaseProperty => $entityId
                    ]
                ];
            }

            $index = 1;
            foreach ($referencedEntities->getEntities() as $referencedEntity) {
                $queries[$table . '_' . $index] = [
                    'query' => 'INSERT INTO `' . $table . '` (',
                ];

                $queries[$table . '_' . $index]['query'] .= $databaseProperty . ', ' . $referencedDatabaseProperty . ') VALUES (';
                $queries[$table . '_' . $index]['query'] .= ':' . $databaseProperty . ', :' . $referencedDatabaseProperty . ')';

                $queries[$table . '_' . $index]['parameters'][':' . $databaseProperty] = $entityId;
                $queries[$table . '_' . $index]['parameters'][':' . $referencedDatabaseProperty] = $referencedEntity->getId();

                $index++;
            }
        }

        return $queries;
    }

    public function search(Criteria $criteria) : array
    {
        $queries = [];

        $queries['parameters'] = [];

        $queries['query'] = 'SELECT ' . $this->getTableName() . '.*, ';

        foreach ($this->getAllProperties() as $property) {
            if(!($property instanceof RelationProperty)) continue;

            $path = $property->getProperty();

            if(in_array($path, $criteria->getRelations()) ||
                $property->isAutoLoad()) {
                $entitySchemaBuilder = $this->getEntitySchemaBuilderByProperty($property);

                $queries['query'] .= $entitySchemaBuilder->getQueryJson($property, $this, $this->getTableName(), $criteria, $path, 0);
            }
        }

        $queries['query'] = rtrim($queries['query'], ', ');
        $queries['query'] .= ' FROM ' . $this->getTableName();

        $limit = '';

        if($criteria->getLimit() > 0) {
            $limit = ' LIMIT ' . $criteria->getLimit();
        }

        $equalsFilter = '';
        $equalsAnyFilter = '';

        $index = 0;
        foreach ($criteria->getFilters() as $filter) {
            switch (get_class($filter)) {
                case EqualsFilter::class:
                    if($equalsFilter != '') {
                        $equalsFilter .= ' AND ';
                    }else{
                        $equalsFilter .= ' ';
                    }

                    $equalsFilter .= $this->getTableName() . '.' . $filter->getProperty() . ' = :' . $filter->getProperty() . $index;
                    $queries['parameters'][':' . $filter->getProperty() . $index] = $filter->getValue();
                    $index++;
                    break;
                case EqualsAnyFilter::class:
                    foreach ($filter->getValues() as $value) {
                        if($equalsAnyFilter != '') {
                            $equalsAnyFilter .= ' OR ';
                        }else{
                            $equalsAnyFilter .= ' ';
                        }

                        $equalsAnyFilter .= $this->getTableName() . '.' . $filter->getProperty() . ' = :' . $filter->getProperty() . $index;
                        $queries['parameters'][':' . $filter->getProperty() . $index] = $value;
                        $index++;
                    }
                    break;
            }
        }

        $where = ' WHERE';

        if($equalsFilter !== '') {
            $where .= $equalsFilter;
        }

        if($equalsAnyFilter !== '') {
            $where .= $equalsAnyFilter;
        }

        if($where !== ' WHERE') {
            $queries['query'] .= $where;
        }

        if($limit !== '') {
            $queries['query'] .= $limit;
        }

        return $queries;
    }

    public function searchIds(Criteria $criteria) : array
    {
        $queries = [];

        $queries['parameters'] = [];

        $queries['query'] = 'SELECT ' . $this->getTableName() . '.id' .
            ' FROM ' . $this->getTableName();

        $where = ' WHERE';
        $limit = '';

        if($criteria->getLimit() > 0) {
            $limit = ' LIMIT ' . $criteria->getLimit();
        }

        $index = 0;
        foreach ($criteria->getFilters() as $filter) {
            switch (get_class($filter)) {
                case EqualsFilter::class:
                    if($where != ' WHERE') {
                        $where .= ' OR ';
                    }else{
                        $where .= ' ';
                    }

                    $where .= $this->getTableName() . '.' . $filter->getProperty() . ' = :' . $filter->getProperty() . $index;
                    $queries['parameters'][':' . $filter->getProperty() . $index] = $filter->getValue();
                    $index++;
                    break;
                case EqualsAnyFilter::class:
                    foreach ($filter->getValues() as $value) {
                        if($where != ' WHERE') {
                            $where .= ' OR ';
                        }else{
                            $where .= ' ';
                        }

                        $where .= $this->getTableName() . '.' . $filter->getProperty() . ' = :' . $filter->getProperty() . $index;
                        $queries['parameters'][':' . $filter->getProperty() . $index] = $value;
                        $index++;
                    }
                    break;
            }
        }

        if($where !== ' WHERE') {
            $queries['query'] .= $where;
        }

        if($limit !== '') {
            $queries['query'] .= $limit;
        }

        return $queries;
    }

    public function searchCount(Criteria $criteria) : array
    {
        $queries = [];

        $queries['parameters'] = [];

        $queries['query'] = 'SELECT COUNT(' . $this->getTableName() . '.id)' .
            ' FROM ' . $this->getTableName();

        $where = ' WHERE';
        $limit = '';

        if($criteria->getLimit() > 0) {
            $limit = ' LIMIT ' . $criteria->getLimit();
        }

        $index = 0;
        foreach ($criteria->getFilters() as $filter) {
            switch (get_class($filter)) {
                case EqualsFilter::class:
                    if($where != ' WHERE') {
                        $where .= ' AND ';
                    }else{
                        $where .= ' ';
                    }

                    $where .= $this->getTableName() . '.' . $filter->getProperty() . ' = :' . $filter->getProperty() . $index;
                    $queries['parameters'][':' . $filter->getProperty() . $index] = $filter->getValue();
                    $index++;
                    break;
                case EqualsAnyFilter::class:
                    foreach ($filter->getValues() as $value) {
                        if($where != ' WHERE') {
                            $where .= ' OR ';
                        }else{
                            $where .= ' ';
                        }

                        $where .= $this->getTableName() . '.' . $filter->getProperty() . ' = :' . $filter->getProperty() . $index;
                        $queries['parameters'][':' . $filter->getProperty() . $index] = $value;
                        $index++;
                    }
                    break;
            }
        }

        if($where !== ' WHERE') {
            $queries['query'] .= $where;
        }

        if($limit !== '') {
            $queries['query'] .= $limit;
        }

        return $queries;
    }

    public function getQueryJson(RelationProperty $referencedByProperty, EntitySchemaBuilder $parentSchemaBuilder, string $parentAs, Criteria $criteria, string $path = '', int $nestingDepth = 0)
    {
        $nestingDepth++;

        $query = "(SELECT CONCAT('[', GROUP_CONCAT(JSON_OBJECT(";

        //dump('$referencedByProperty', $referencedByProperty, '$parentSchemaBuilder', $parentSchemaBuilder, '$schema', $this, '$repository', $this->repository);

        foreach ($this->getAllProperties() as $property) {
            if(!($property instanceof RelationProperty)) {
                $query .= "'" . $property->getProperty() . "', " . $referencedByProperty->getProperty() . "." . $property->getProperty() . ", ";

                continue;
            }

            if($property instanceof ManyToOneProperty ||
            $property instanceof OneToOneProperty) {
                $query .= "'" . $property->getProperty() . "_id', " . $referencedByProperty->getProperty() . "." . $property->getProperty() . "_id, ";
            }

            if($nestingDepth > self::$maximumNestingDepth) continue;

            $newPath = $path . '.' . $property->getProperty();

            if(in_array($newPath, $criteria->getRelations()) ||
                ($property->isAutoLoad() && $nestingDepth == 1)) {
                $entitySchemaBuilder = $this->getEntitySchemaBuilderByProperty($property);

                $query .= "'" . $property->getProperty() . "', " .
                    $entitySchemaBuilder->getQueryJson(
                        $property,
                        $this,
                        $referencedByProperty->getProperty(),
                        $criteria,
                        $newPath,
                        $nestingDepth
                    ) . ", ";
            }
        }

        $query = rtrim($query, ', ');

        $query .= ")), ']') FROM " . $this->getTableName() . " " . $referencedByProperty->getProperty();

        $limit = '';
        $join = '';
        $where = '';

        //echo "<br><span style='color: lightcoral;'>" . get_class($referencedByProperty) . "</span><br>";
        switch (get_class($referencedByProperty)) {
            case OneToOneProperty::class:
                $referencedProperty = $this->getReferencedOneToManyPropertyByReferencedByProperty($referencedByProperty);

                $table = $parentAs;

                if($referencedProperty != null && $nestingDepth > 1) {
                    $table = $referencedProperty->getProperty();
                }

                //echo "<br><span style='color: yellow;'>ONE-TO-ONE " . $referencedByProperty->getProperty() . "</span><br>";
                $limit = ' LIMIT 1';
                $where = ' WHERE ' . $referencedByProperty->getProperty() . '.id = ' . $table . '.' . $referencedByProperty->getProperty() . '_id';
                break;
            case ManyToOneProperty::class:
                $referencedProperty = $this->getReferencedOneToManyPropertyByReferencedByProperty($referencedByProperty);

                $table = $parentAs;

                if($referencedProperty != null && $nestingDepth > 1) {
                    $table = $referencedProperty->getProperty();
                }

                //echo "<br><span style='color: lime;'>MANY-TO-ONE " . $referencedByProperty->getProperty() . "</span><br>";
                $where = ' WHERE ' . $referencedByProperty->getProperty() . '.id = ' . $table . '.' . $referencedByProperty->getProperty() . '_id';
                break;
            case OneToManyProperty::class:
                //echo "<br><span style='color: gold;'>ONE-TO-MANY " . $referencedByProperty->getProperty() . "</span><br>";

                $table = $parentAs;

                $where = ' WHERE ' . $referencedByProperty->getProperty() . '.' . $referencedByProperty->getReferencedProperty() . '_id' . ' = ' . $table . '.id';
                break;
            case ManyToManyProperty::class:
                //echo "<br><span style='color: skyblue;'>MANY-TO-ONE " . $referencedByProperty->getProperty() . "</span><br>";

                $table = $parentAs;

                $manyToManyTables = $parentSchemaBuilder->getManyToManyTables([$referencedByProperty]);
                $properties = $parentSchemaBuilder->getManyToManyDatabasePropertiesByProperty($referencedByProperty);

                $join = ' JOIN ' . $manyToManyTables[0] . ' ON ' . $manyToManyTables[0] . '.' . $properties[1] . ' = ' . $referencedByProperty->getProperty() . '.id';
                $where = ' WHERE ' . $manyToManyTables[0] . '.' . $properties[0] . ' = ' . $table . '.id';

                break;
        }

        $query .= $join . $where . $limit . ')';

        if($nestingDepth == 1) {
            $query .= " AS " . $referencedByProperty->getProperty() . ", ";
        }

        return $query;
    }

    private function getReferencedOneToManyPropertyByReferencedByProperty(ManyToOneProperty|OneToOneProperty $referencedByProperty) : OneToManyProperty|null
    {
        $repository = $this->getEntityRepositoryByProperty($referencedByProperty);

        foreach ($repository->getEntityDefinition()->getPropertyDefinition() as $property) {
            //if(!($property instanceof RelationProperty)) continue;

            if(!($property instanceof OneToManyProperty)) continue;

            if($property->getReferencedProperty() === $referencedByProperty->getProperty()) {
                return $property;
            }
        }

        return null;
    }

    private function getEntityRepositoryByProperty(RelationProperty $property) : EntityRepository
    {
        if(!array_key_exists($property->getReferencedEntity() . '.repository', self::$repositories)) {
            $repository = $this->container->get($property->getReferencedEntity() . '.repository');

            self::$repositories[$property->getReferencedEntity() . '.repository'] = $repository;
        }

        return self::$repositories[$property->getReferencedEntity() . '.repository'];
    }

    private function getEntitySchemaBuilderByProperty(RelationProperty $property) : EntitySchemaBuilder
    {
        if(!array_key_exists($property->getReferencedEntity() . '.schema', self::$repositories)) {
            $entitySchemaBuilder = self::getEntitySchemaBuilder($this->getEntityRepositoryByProperty($property), null, $this->container);

            self::$repositories[$property->getReferencedEntity() . '.schema'] = $entitySchemaBuilder;
        }

        return self::$repositories[$property->getReferencedEntity() . '.schema'];
    }

    /**
     * @return Entity
     */
    public function getEntity(): Entity
    {
        return $this->entity;
    }

    /**
     * @return EntityRepository
     */
    public function getRepository(): EntityRepository
    {
        return $this->repository;
    }

    /**
     * @return ReflectionClass
     */
    private function getReflectionClass(): ReflectionClass
    {
        if ($this->reflectionClass == null) {
            $this->reflectionClass = new ReflectionClass(get_class($this->entity));
        }

        return $this->reflectionClass;
    }

    public function getPropertyNames() : array
    {
        $propertyNames = [];

        foreach ($this->getAllProperties() as $property) {
            if(!($property instanceof Property)) continue;
            if($property instanceof OneToManyProperty) continue;
            if($property instanceof ManyToManyProperty) continue;

            $propertyNames[] = $this->convertToDatabaseProperty($property->getProperty());
        }

        return $propertyNames;
    }

    public function getCollectionPropertyNames() : array
    {
        $propertyNames = [];

        foreach ($this->getCollectionProperties() as $property) {
            $propertyNames[] = $this->convertToDatabaseProperty($property->getProperty());
        }

        return $propertyNames;
    }

    public function getParameters() : array
    {
        $parameters = [];

        //dd(get_class_vars($this->entity));

        return $parameters;
    }

    /**
     * @return array
     */
    public static function getEntitySchemaBuilder(EntityRepository $repository, ?Entity $entity, Container $container): EntitySchemaBuilder
    {
        $key = $repository->getEntityDefinition()->getEntityName();
        if($entity != null) {
            $key = $repository->getEntityDefinition()->getEntityName() . $entity->getId();
        }

        if(!array_key_exists($key, self::$entitySchemaBuilders)) {
            $entitySchemaBuilder = new self($repository, $entity, $container);
            self::$entitySchemaBuilders[$key] = $entitySchemaBuilder;
        }

        return self::$entitySchemaBuilders[$key];
    }

}