<?php

namespace NewDavis\DatabaseManagement\Core\Entity;

use Composer\Semver\Constraint\MultiConstraint;
use NewDavis\DatabaseManagement\Core\Driver\Connection;
use NewDavis\DatabaseManagement\Core\Driver\Statement;
use NewDavis\DatabaseManagement\Core\Entity\Exception\PropertyNotFoundInEntityException;
use NewDavis\DatabaseManagement\Core\Entity\Exception\RequiredPropertyNotFoundInEntityDataSetException;
use NewDavis\DatabaseManagement\Core\Entity\Field\DateTimeField;
use NewDavis\DatabaseManagement\Core\Entity\Field\Field;
use NewDavis\DatabaseManagement\Core\Entity\Flag\AutoIncrement;
use NewDavis\DatabaseManagement\Core\Entity\Flag\PrimaryKey;
use NewDavis\DatabaseManagement\Core\Entity\Flag\Required;
use NewDavis\DatabaseManagement\Core\Schema\SchemaBuilder;
use NewDavis\DatabaseManagement\Core\Search\Criteria\Criteria;
use NewDavis\DatabaseManagement\Core\Search\Criteria\Filter\EqualsAnyFilter;
use ReflectionClass;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * @template TElement
 */
class EntityRepository
{

    public function __construct(
        private readonly string $definition,
        #[Autowire(service: Connection::class)] private readonly Connection $connection
    ) {
    }

    /**
     * @param EntityCollection<TElement>|TElement|array $entities
     * @return void
     */
    public function upsert(EntityCollection|Entity|array $entities)
    {
        if($entities instanceof Entity) {
            $entities = new EntityCollection([$entities]);
        }

        $criteria = new Criteria();

        if(is_array($entities)) {
            $criteria->addFilter(new EqualsAnyFilter('id', array_map(
                fn($dataSet) => $dataSet['id'],
                $entities
            )));
        }else{
            $criteria->addFilter(new EqualsAnyFilter('id', $entities->getIds()));
        }

        $existingIds = $this->searchIds($criteria);

        foreach (($entities instanceof EntityCollection ? $entities->getEntities() : $entities) as $entity)
        {
            if(in_array(
                ($entity instanceof Entity ? $entity->getId() : $entity['id']),
                $existingIds
            )) {
                $this->update($entity);
            } else {
                $this->create($entity);
            }
        }
    }

    /**
     * @param EntityCollection<TElement>|TElement|array $entities
     * @return void
     */
    public function create(EntityCollection|Entity|array $entities)
    {
        // convert $entities to EntityCollection
        if($entities instanceof Entity) {
            $entities = new EntityCollection([$entities]);
        }else if(is_array($entities)) {
            $entities = $this->buildEntityCollection($entities);
        }

        $createStatements = SchemaBuilder::create($this->definition, $entities);

        /*$statement = new Statement();
        $statement->setStatement(
            implode(PHP_EOL, array_map(
                fn($createStatement) => $createStatement->getStatement(),
                $createStatements
            ))
        );

        foreach ($createStatements as $createStatement) {
            foreach ($createStatement->getParameters() as $parameter) {
                $statement->addParameter('?', $parameter);
            }
        }*/

        $results = [];
        foreach ($createStatements as $createStatement) {
            var_dump($createStatement);
            $results[] = $this->connection->prepare($createStatement);
        }
        dd($results);
    }

    /**
     * @param EntityCollection|Entity|array $entities
     * @param array|null $entityDataSets
     * @return void
     * @throws PropertyNotFoundInEntityException
     * @throws RequiredPropertyNotFoundInEntityDataSetException
     */
    public function update(EntityCollection|Entity|array $entities)
    {
        // convert $entities to EntityCollection
        if($entities instanceof Entity) {
            $entities = new EntityCollection([$entities]);
        }else if(is_array($entities)) {
            $entities = $this->buildEntityCollection($entities, true);
        }

        $updateStatements = SchemaBuilder::update($this->definition, $entities);

        dd($updateStatements);
    }

    public function delete(Criteria $criteria)
    {
        $deleteQuery = SchemaBuilder::delete($this->definition, $criteria);

        dd($deleteQuery);
    }

    public function count(Criteria $criteria): int
    {
        $searchQuery = SchemaBuilder::search($this->definition, $criteria);

        return 0;
    }

    /**
     * @param Criteria $criteria
     * @return EntityCollection<TElement>
     */
    public function search(Criteria $criteria): EntityCollection
    {
        $statement = SchemaBuilder::search($this->definition, $criteria);

        $statementResult = $this->connection->prepare($statement);

        return $this->buildEntityCollection($statementResult);
    }

    /**
     * @param Criteria $criteria
     * @return array|null
     */
    public function searchIds(Criteria $criteria): array|null
    {
        $statement = SchemaBuilder::searchIds($this->definition, $criteria);

        $statementResult = $this->connection->prepare($statement);

        return array_map(
            fn ($dataSet) => $dataSet['id'],
            $statementResult
        );
    }

    /**
     * @return TElement
     */
    private function buildEntityCollection(array $entityDataSets, $ignoreErrors = false) : EntityCollection
    {
        $entityClass = $this->definition::getEntityClass();
        $fields = $this->definition::getDefinitionFields();

        $entities = new EntityCollection();

        foreach ($entityDataSets as $entityDataSet) {
            /** @var Entity $entity */
            $entity = new $entityClass();
            $reflectionEntity = new ReflectionClass($entityClass);

            /** @var Field $field */
            foreach ($fields as $field) {
                $required = ($field->hasFlag(Required::class) ||
                    $field->hasFlag(PrimaryKey::class)) &&
                    !$field->hasFlag(AutoIncrement::class);

                $storageName = array_key_exists($field->getStorageName(), $entityDataSet) ?
                    $field->getStorageName() :
                    (array_key_exists($field->getInternalName(), $entityDataSet) ?
                        $field->getInternalName() :
                        null
                    );

                if(!$storageName && !$reflectionEntity->hasProperty($field->getInternalName())) {
                    if($required && !$ignoreErrors) {
                        throw new RequiredPropertyNotFoundInEntityDataSetException($field->getInternalName());
                    } else if(!$ignoreErrors) {
                        throw new PropertyNotFoundInEntityException($field->getInternalName(), $entityClass);
                    }
                }

                $property = $reflectionEntity->getProperty($field->getInternalName());
                if(!$storageName && $required && !$property->isInitialized($entity) && (!$ignoreErrors)) {
                    throw new RequiredPropertyNotFoundInEntityDataSetException($field->getInternalName());
                }else if(!$storageName) {
                    continue;
                }

                $value = null;

                switch ($property->getType()->getName()) {
                    case "string":
                        $value = (string) $entityDataSet[$storageName];
                        break;
                    case "bool":
                        $value = (bool)$entityDataSet[$storageName];
                        break;
                    case "int":
                        $value = (int)$entityDataSet[$storageName];
                        break;
                    case "DateTimeImmutable":
                        if($entityDataSet[$storageName] instanceof \DateTimeImmutable) {
                            $value = $entityDataSet[$storageName];
                            break;
                        }

                        $dateTime = \DateTimeImmutable::createFromFormat(DateTimeField::FORMAT, $entityDataSet[$storageName]);
                        if($dateTime) {
                            $value = $dateTime;
                        }
                        break;
                    case "array":
                        if($field->getType() !== 'JSON') break;

                        if(is_array($entityDataSet[$storageName])) {
                            $value = $entityDataSet[$storageName];
                        }

                        $array = json_decode($entityDataSet[$storageName], true);
                        if(json_last_error() === JSON_ERROR_NONE) {
                            $value = $array;
                        }

                        break;
                    default:
                        dd($property->getType()->getName());
                        break;
                }

                $property->setValue($entity, $value);
            }

            $entities->add($entity);
        }

        return $entities;
    }

}