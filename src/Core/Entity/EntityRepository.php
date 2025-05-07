<?php

namespace NewDavis\DatabaseManagement\Core\Entity;

use NewDavis\DatabaseManagement\Core\Driver\Connection;
use NewDavis\DatabaseManagement\Core\Entity\Exception\PropertyNotFoundInEntityException;
use NewDavis\DatabaseManagement\Core\Entity\Exception\RequiredPropertyNotFoundInEntityDataSetException;
use NewDavis\DatabaseManagement\Core\Entity\Exception\UnableToFindMatchingFkFieldForRelatedFieldException;
use NewDavis\DatabaseManagement\Core\Entity\Field\DateTimeField;
use NewDavis\DatabaseManagement\Core\Entity\Field\Field;
use NewDavis\DatabaseManagement\Core\Entity\Field\FkField;
use NewDavis\DatabaseManagement\Core\Entity\Field\Relation\ManyToManyRelation;
use NewDavis\DatabaseManagement\Core\Entity\Field\Relation\ManyToOneRelation;
use NewDavis\DatabaseManagement\Core\Entity\Field\Relation\OneToManyRelation;
use NewDavis\DatabaseManagement\Core\Entity\Field\Relation\OneToOneRelation;
use NewDavis\DatabaseManagement\Core\Entity\Field\Relation\RelationField;
use NewDavis\DatabaseManagement\Core\Entity\Flag\AutoIncrement;
use NewDavis\DatabaseManagement\Core\Entity\Flag\PrimaryKey;
use NewDavis\DatabaseManagement\Core\Entity\Flag\Required;
use NewDavis\DatabaseManagement\Core\Schema\SchemaBuilder;
use NewDavis\DatabaseManagement\Core\Search\Criteria\Criteria;
use NewDavis\DatabaseManagement\Core\Search\Criteria\Filter\EqualsAnyFilter;
use ReflectionClass;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Container;

/**
 * @template TElement
 */
class EntityRepository
{

    /**
     * @param string $definition
     * @param Connection $connection
     */
    public function __construct(
        private readonly string $definition,
        #[Autowire(service: Connection::class)] private readonly Connection $connection,
        #[Autowire(service: 'service_container')] private readonly Container $container,
    ) {
    }

    /**
     * @param EntityCollection<TElement>|TElement|array $entities
     * @return void
     */
    public function upsert(EntityCollection|Entity|array $entities)
    {
        if($entities instanceof Entity) {
            $collectionClass = $this->getDefinition()::getCollectionClass();
            $entities = new $collectionClass([$entities]);
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

        $entities = ($entities instanceof EntityCollection ? $entities->getEntities() : $entities);
        foreach ($entities as $entity)
        {
            $entityId = ($entity instanceof Entity ? $entity->getId() : $entity['id']);
            if(in_array($entityId, $existingIds)) {
                $this->update([$entity]);
            } else {
                $this->create([$entity]);
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
            $collectionClass = $this->getDefinition()::getCollectionClass();
            $entities = new $collectionClass([$entities]);
        }else if(is_array($entities)) {
            $createdProperties = $entities;
            $entities = $this->buildEntityCollection($entities);
        }

        $this->processEntitiesBeforePersist($entities, $createdProperties);

        $createStatements = SchemaBuilder::create($this->definition, $entities);

        foreach ($createStatements as $createStatement) {
            $this->connection->prepare($createStatement);
        }
    }

    /**
     * @param EntityCollection<TElement>|TElement|array $entities
     * @return void
     */
    public function update(EntityCollection|Entity|array $entities)
    {
        // convert $entities to EntityCollection
        if($entities instanceof Entity) {
            $collectionClass = $this->getDefinition()::getCollectionClass();
            $entities = new $collectionClass([$entities]);
        }else if(is_array($entities)) {
            $changedProperties = $entities;
            $entities = $this->buildEntityCollection($entities, true);
        }

        foreach ($entities->getEntities() as $entity) {
            $entity->setUpdatedAt(new \DateTimeImmutable());
        }

        $changedProperties = $this->processEntitiesBeforePersist($entities, $changedProperties);

        $updateStatements = SchemaBuilder::update($this->definition, $entities, $changedProperties);

        foreach ($updateStatements as $updateStatement) {
            $this->connection->prepare($updateStatement);
        }
    }

    /**
     * Check for Relations to be saved. (ManyToMany, ManyToOne, OneToOne, OneToMany)
     * @param EntityCollection $entityCollection
     * @return void
     */
    private function processEntitiesBeforePersist(EntityCollection $entityCollection, ?array $properties = null)
    {
        if(!$properties) {
            $properties = array_map(
                fn($entity) => $entity->jsonSerialize(),
                $entityCollection->getEntities()
            );
        }

        $relationFields = array_filter(
            $this->getDefinition()::getDefinitionFields(),
            fn($field) => ($field instanceof RelationField)
        );

        foreach ($entityCollection->getEntities() as $index => $entity) {
            $props = $properties[$index];

            $affectedRelationFields = $this->getAffectedRelationsFromChangedProperties($props, $relationFields);
            if(count($affectedRelationFields) == 0) continue;

            foreach ($affectedRelationFields as $property => $relationField) {
                $relationValue = $props[$property];

                /** @var EntityRepository $relatedRepository */
                $relatedRepository = $this->container->get(
                    $relationField->getRelatedToDefinition()::getEntityName() . '.repository'
                );

                switch (get_class($relationField)) {
                    case ManyToOneRelation::class:
                    case OneToOneRelation::class:
                        // find the matching ForeignKeyField
                        $foreignKeyField = array_values(array_filter(
                            $this->getDefinition()::getDefinitionFields(),
                            fn($field) => $field instanceof FkField &&
                                $field->getStorageName() == $relationField->getStorageName()
                        ));

                        if(count($foreignKeyField) == 0) {
                            throw new UnableToFindMatchingFkFieldForRelatedFieldException(
                                $relationField->getStorageName(),
                                $this->definition
                            );
                        }

                        $foreignKeyField = $foreignKeyField[0];

                        // upsert the related value for example it upserts the primaryRole when upserting account
                        $relatedRepository->upsert([$relationValue]);

                        $reflectionEntity = new ReflectionClass($entity);

                        if($reflectionEntity->hasProperty($foreignKeyField->getInternalName())) {
                            // write to foreignkey field property e.g. primaryRoleId
                            $reflectionEntity->getProperty($foreignKeyField->getInternalName())
                                ->setValue(
                                    $entity,
                                    $relationValue[$relationField->getRelatedToInternalName()]
                                );

                            // add foreignkey field property to changed properties for update call.
                            $properties[$index][
                                $foreignKeyField->getInternalName()
                            ] = $relationValue[$relationField->getRelatedToInternalName()];
                        }

                        break;
                    case ManyToManyRelation::class:
                        // upsert the related values for example it upserts the roles when upserting account
                        $relatedRepository->upsert($relationValue);

                        $id = $properties[$index][$relationField->getRelatedByInternalName()];
                        $relatedIds = array_map(
                            fn($entityJson) => $entityJson[$relationField->getRelatedToInternalName()],
                            $relationValue
                        );



                        dd("ManyToMany", $id, $relatedIds, $relationField, $relationValue, $relatedRepository, $entity);
                        break;
                    case OneToManyRelation::class:
                        dd("OneToMany");
                        break;
                }
            }
        }

        return $properties;
    }

    /**
     * @param array $changedProperties
     * @param array<RelationField> $fields
     * @return array<RelationField>
     */
    private function getAffectedRelationsFromChangedProperties(array $changedProperties, array $fields) : array
    {
        $found = [];

        foreach (array_keys($changedProperties) as $changedProperty) {
            foreach ($fields as $field) {
                if($field->getInternalName() === $changedProperty) {
                    $found[$changedProperty] = $field;
                }
            }
        }

        return $found;
    }

    /**
     * @param Criteria $criteria
     * @return void
     */
    public function delete(Criteria $criteria)
    {
        $deleteQuery = SchemaBuilder::delete($this->definition, $criteria);

        $this->connection->prepare($deleteQuery);
    }

    /**
     * @param Criteria $criteria
     * @return int
     */
    public function count(Criteria $criteria): int
    {
        $countQuery = SchemaBuilder::count($this->definition, $criteria);

        $result = $this->connection->prepare($countQuery);

        return array_values($result[0])[0];
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
     * @return EntityCollection<TElement>
     */
    private function buildEntityCollection(array $entityDataSets, bool $ignoreErrors = false): EntityCollection
    {
        $collectionClass = $this->getDefinition()::getCollectionClass();
        $entities = new $collectionClass();

        foreach ($entityDataSets as $entityDataSet) {
            $entities->add($this->buildEntity($entityDataSet, $ignoreErrors));
        }

        return $entities;
    }

    /**
     * @param array $entityDataSet
     * @param bool $ignoreErrors
     * @return TElement
     * @throws PropertyNotFoundInEntityException
     * @throws RequiredPropertyNotFoundInEntityDataSetException
     * @throws \ReflectionException
     */
    private function buildEntity(array $entityDataSet, bool $ignoreErrors = false): Entity
    {
        $entityClass = $this->definition::getEntityClass();
        $fields = $this->definition::getDefinitionFields();

        /** @var Entity $entity */
        $entity = new $entityClass();
        $reflectionEntity = new ReflectionClass($entityClass);

        /** @var Field $field */
        foreach ($fields as $field) {
            if($field instanceof RelationField) continue;

            // determine if the field is required or not.
            $required = ($field->hasFlag(Required::class) ||
                    $field->hasFlag(PrimaryKey::class)) &&
                !$field->hasFlag(AutoIncrement::class);

            // get storageName by storageName or internalName if the dataset is entered using upsert.
            $storageName = $this->getStorageName($field, $entityDataSet);
            $internalName = $this->getInternalName($field, $entityDataSet);

            // check if entity has property.
            if(!$storageName && !$reflectionEntity->hasProperty($internalName)) {
                if($required && !$ignoreErrors) {
                    throw new RequiredPropertyNotFoundInEntityDataSetException($internalName);
                } else if(!$ignoreErrors) {
                    throw new PropertyNotFoundInEntityException($internalName, $entityClass);
                }
            }

            $property = $reflectionEntity->getProperty($internalName);
            // check if required property is not initialized
            if(!$storageName && $required && !$property->isInitialized($entity) && (!$ignoreErrors)) {
                throw new RequiredPropertyNotFoundInEntityDataSetException($internalName);
            }else if(!$storageName) {
                continue;
            }

            $property->setValue($entity, $this->convertValue(
                $entityDataSet,
                $storageName,
                $property,
                $field
            ));
        }

        return $entity;
    }

    /**
     * @param array $entityDataSet
     * @param string $storageName
     * @param \ReflectionProperty $property
     * @param Field $field
     * @return array|bool|\DateTimeImmutable|int|mixed|string|null
     */
    private function convertValue(
        array $entityDataSet,
        string $storageName,
        \ReflectionProperty $property,
        Field $field
    ) {
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
                    break;
                }

                $array = json_decode($entityDataSet[$storageName], true);
                if(json_last_error() === JSON_ERROR_NONE) {
                    $value = $array;
                }

                break;
            default:
                dd($property);
                break;
        }

        return $value;
    }

    private function getStorageName(Field $field, array $entityDataSet)
    {
        $storageName = null;

        if($field instanceof RelationField) {
            switch (get_class($field)) {
                case OneToOneRelation::class:
                case ManyToOneRelation::class:
                    if(array_key_exists($field->getStorageName(), $entityDataSet)) {
                        $storageName = $field->getStorageName();
                    }else if(array_key_exists($field->getInternalName(), $entityDataSet)) {
                        $storageName = $field->getInternalName();
                    }
                    break;
                case OneToManyRelation::class:
                case ManyToManyRelation::class:
                    if(array_key_exists($field->getRelationStorageName(), $entityDataSet)) {
                        $storageName = $field->getRelationStorageName();
                    }else if(array_key_exists($field->getRelationInternalName(), $entityDataSet)) {
                        $storageName = $field->getRelationInternalName();
                    }
                    break;
            }
        }else{
            if(array_key_exists($field->getStorageName(), $entityDataSet)) {
                $storageName = $field->getStorageName();
            }else if(array_key_exists($field->getInternalName(), $entityDataSet)) {
                $storageName = $field->getInternalName();
            }
        }

        return $storageName;
    }

    private function getInternalName(Field $field, array $entityDataSet)
    {
        $internalName = null;

        if($field instanceof RelationField) {
            $internalName = $field->getRelationInternalName();
        }else{
            $internalName = $field->getInternalName();
        }

        return $internalName;
    }

    /**
     * @return string
     */
    public function getDefinition(): string
    {
        return $this->definition;
    }

}