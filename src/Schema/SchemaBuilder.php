<?php

namespace NewDavis\DatabaseManagement\Schema;

use NewDavis\DatabaseManagement\Core\Entity\Entity;
use NewDavis\DatabaseManagement\Core\Entity\EntityDefinition;
use NewDavis\DatabaseManagement\Core\Entity\Property\CreatedAtProperty;
use NewDavis\DatabaseManagement\Core\Entity\Property\IdProperty;
use NewDavis\DatabaseManagement\Core\Entity\Property\Property;
use NewDavis\DatabaseManagement\Core\Entity\Property\Relation\ManyToManyProperty;
use NewDavis\DatabaseManagement\Core\Entity\Property\Relation\OneToManyProperty;
use NewDavis\DatabaseManagement\Core\Entity\Property\Relation\RelationProperty;
use NewDavis\DatabaseManagement\Core\Entity\Property\UpdatedAtProperty;
use DateTime;
use Doctrine\ORM\Mapping\OneToMany;
use ReflectionClass;

class SchemaBuilder
{

    public function __construct(private EntityDefinition $definition)
    {}

    private array|null $properties = null;

    protected function getTableName() : string
    {
        return $this->definition->getEntityName();
    }

    protected function hash(string $value) : string
    {
        return substr(strtoupper(md5($value)), 0, 8);
    }

    /*public function getTablesWithProperties(array $tables = []) : array
    {
        foreach ($this->getAllProperties() as $property) {
            if (!($property instanceof RelationProperty)) continue;

            if(!isset($tables[$this->definition->getEntityName()])) {
                $tables[$this->definition->getEntityName()] = [];
            }

            if($property instanceof ManyToManyProperty) {
                array_push($tables[$this->definition->getEntityName()], $this->definition->getEntityName() . '_' . $property->getReferencedEntity());
            } else {
                array_push($tables[$this->definition->getEntityName()], $property->getReferencedEntity());
            }
        }

        return $tables;
    }*/

    public function getTables() : array
    {
        $tables = [$this->definition->getEntityName()];

        foreach ($this->getAllProperties() as $property) {
            if(!($property instanceof RelationProperty)) continue;

            if($property instanceof ManyToManyProperty) {
                $table = $this->definition->getEntityName() . '_' . $property->getReferencedEntity();
            } else {
                $table = $property->getReferencedEntity();
            }

            if(!in_array($table, $tables)) {
                $tables[] = $table;
            }
        }

        return $tables;
    }

    public function getTable() : string
    {
        return $this->definition->getEntityName();
    }

    public function getAllProperties() : array
    {
        if($this->properties == null) {
            $this->properties = [
                new IdProperty()
            ];

            $this->properties = array_merge($this->properties, $this->definition->getPropertyDefinition());

            $this->properties[] = new CreatedAtProperty();
            $this->properties[] = new UpdatedAtProperty();
        }

        return $this->properties;
    }

    public function getProperties() : array
    {
        $properties = [];

        foreach ($this->getAllProperties() as $property) {
            if($property instanceof ManyToManyProperty) continue;
            if($property instanceof OneToManyProperty) continue;

            $properties[] = $property;
        }

        return $properties;
    }

    public function getDatabaseProperties() : array
    {
        $properties = [];

        foreach ($this->getAllProperties() as $property) {
            if($property instanceof ManyToManyProperty) continue;
            if($property instanceof OneToManyProperty) continue;

            $propertyExtension = '';
            if($property instanceof RelationProperty) {
                $propertyExtension = '_id';
            }

            $properties[] = $property->getProperty() . $propertyExtension;
        }

        return $properties;
    }

    public function getPropertiesWithValues(Entity $entity) : array
    {
        $reflectionEntity = new ReflectionClass($entity);

        $properties = [];

        foreach ($this->getAllProperties() as $property) {
            if(!($property instanceof Property)) continue;

            if(!$reflectionEntity->hasProperty($property->getProperty())) continue;

            $classProperty = $reflectionEntity->getProperty($property->getProperty());

            if($classProperty->isInitialized($entity)) {
                $properties[$property->getProperty()] = $classProperty->getValue($entity);
            }
        }

        return $properties;
    }

    protected function getCollectionProperties() : array
    {
        $collectionProperties = [];

        foreach ($this->getAllProperties() as $property) {
            if (!($property instanceof Property)) continue;
            if (!($property instanceof OneToManyProperty) && !($property instanceof ManyToManyProperty)) continue;

            $collectionProperties[] = $property;
        }

        return $collectionProperties;
    }

    protected function getManyToManyProperties() : array
    {
        $manyToManyProperties = [];

        foreach ($this->getAllProperties() as $property) {
            if (!($property instanceof Property)) continue;
            if (!($property instanceof ManyToManyProperty)) continue;

            $manyToManyProperties[] = $property;
        }

        return $manyToManyProperties;
    }

    public function getManyToManyDatabasePropertiesByProperty(ManyToManyProperty $property) : array
    {
        $result = [];

        if($property->isMain()) {
            $result[] = $this->getTableName() . '_id';
            $result[] = $property->getReferencedEntity() . '_id';
        }else{
            $result[] = $property->getReferencedEntity() . '_id';
            $result[] = $this->getTableName() . '_id';
        }

        return $result;
    }

    public function getManyToManyDatabaseProperties() : array
    {
        $properties = [];

        foreach ($this->getAllProperties() as $property) {
            if (!($property instanceof ManyToManyProperty)) continue;

            $propertyExtension = '_id';

            $properties[] = $this->getTableName() . $propertyExtension;
            $properties[] = $property->getReferencedEntity() . $propertyExtension;
        }

        return $properties;
    }

    public function convertToDatabaseProperty(Property|string $property, string $propertyExtension = '_id') : string
    {
        if(is_string($property)) return $property . $propertyExtension;
        if(!($property instanceof RelationProperty)) return $property->getProperty();

        return $property->getProperty() . $propertyExtension;
    }

    public function getCollectionPropertyValue(Entity $entity, ManyToManyProperty|OneToManyProperty $property) : array
    {
        $reflectionEntity = new ReflectionClass($entity);

        return $reflectionEntity->getProperty($property->getProperty())->getValue($entity);
    }

    protected function hasManyToManyRelation() : bool
    {
        foreach ($this->getAllProperties() as $property) {
            if($property instanceof ManyToManyProperty) return true;
        }

        return false;
    }

    protected function getManyToManyTables(array $manyToManyProperties) : array
    {
        $manyToManyTables = [];

        foreach ($manyToManyProperties as $property) {
            $firstEntity = $property->isMain() ? $this->definition->getEntityName() : $property->getReferencedEntity();
            $secondEntity = $property->isMain() ? $property->getReferencedEntity() : $this->definition->getEntityName();

            $tableName = $firstEntity . '_' . $secondEntity;

            $table = $tableName;

            $manyToManyTables[] = $table;
        }

        return $manyToManyTables;
    }

    protected static function sortTables(array... $tables) : array
    {
        $sortedTables = [];

        $withChildrenIndex = 1000;
        $withoutChildrenIndex = 0;

        foreach ($tables as $table) {
            foreach ($table as $key => $value) {
                if(array_key_exists($key, $table)) {
                    // has children
                    $sortedTables[$key] = $withChildrenIndex;

                    $withChildrenIndex .= 1000;
                } else {
                    // no children
                    $sortedTables[$key] = $withoutChildrenIndex;

                    $withoutChildrenIndex .= 1;
                }
            }
        }

        return $sortedTables;
    }

    public static function merge(array... $queriesArray)
    {
        $finalQueries = [];

        $foreignKeys = [];

        foreach ($queriesArray as $queries) {
            foreach ($queries as $query) {
                if(is_array($query)) {
                    foreach ($query as $s) {
                        $foreignKeys[] = $s;
                    }
                }else{
                    $finalQueries[] = $query;
                }
            }
        }

        return array_merge($finalQueries, $foreignKeys);
    }

    public static function getDate()
    {
        $datetime = new DateTime();

        return $datetime->format('Y-m-d H:i:s.u');
    }

}