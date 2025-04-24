<?php

namespace NewDavis\DatabaseManagement\Core\Entity;

use NewDavis\DatabaseManagement\Core\Driver\Connection;
use NewDavis\DatabaseManagement\Core\Entity\Exception\PropertyNotFoundInEntityException;
use NewDavis\DatabaseManagement\Core\Entity\Field\DateTimeField;
use NewDavis\DatabaseManagement\Core\Entity\Field\Field;
use NewDavis\DatabaseManagement\Core\Schema\SchemaBuilder;
use NewDavis\DatabaseManagement\Core\Search\Criteria\Criteria;
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

    public function upsert()
    {

    }

    public function create()
    {

    }

    public function update()
    {

    }

    public function delete()
    {

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

        $entityCollection = $this->buildEntityCollection($statementResult);

        return $entityCollection;
    }

    /**
     * @param Criteria $criteria
     * @return EntityCollection<TElement>
     */
    public function searchIds(Criteria $criteria): EntityCollection
    {
        return new EntityCollection();
    }

    /**
     * @return TElement
     */
    private function buildEntityCollection(array $entityDataSets) : EntityCollection
    {
        $entityClass = $this->definition::getEntityClass();
        $fields = $this->definition::getDefinitionFields();

        $entities = new EntityCollection();

        foreach ($entityDataSets as $entityDataSet) {
            /** @var Entity $entity */
            $entity = new $entityClass(true);
            $reflectionEntity = new ReflectionClass($entityClass);

            /** @var Field $field */
            foreach ($fields as $field) {
                if(!array_key_exists($field->getStorageName(), $entityDataSet)) continue;
                if(!$reflectionEntity->hasProperty($field->getInternalName())) {
                    throw new PropertyNotFoundInEntityException($field->getInternalName(), $entityClass);
                }

                $property = $reflectionEntity->getProperty($field->getInternalName());
                $value = null;

                switch ($property->getType()->getName()) {
                    case "string":
                        $value = (string) $entityDataSet[$field->getStorageName()];
                        break;
                    case "bool":
                        $value = (bool)$entityDataSet[$field->getStorageName()];
                        break;
                    case "int":
                        $value = (int)$entityDataSet[$field->getStorageName()];
                        break;
                    case "DateTimeImmutable":
                        $dateTime = \DateTimeImmutable::createFromFormat(DateTimeField::FORMAT, $entityDataSet[$field->getStorageName()]);
                        if($dateTime) {
                            $value = $dateTime;
                        }
                        break;
                    case "array":
                        if($field->getType() !== 'JSON') break;

                        $array = json_decode($entityDataSet[$field->getStorageName()], true);
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