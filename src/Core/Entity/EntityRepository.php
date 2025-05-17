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
use NewDavis\DatabaseManagement\Core\Search\Criteria\Filter\EqualsFilter;
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
     * @throws UnableToFindMatchingFkFieldForRelatedFieldException
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
                $this->update($entity->jsonSerialize());
            } else {
                $this->create($entity->jsonSerialize());
            }
        }
    }

    /**
     * @param EntityCollection<TElement>|TElement|array $entities
     * @return void
     * @throws UnableToFindMatchingFkFieldForRelatedFieldException
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

        $this->processEntities($entities, true, $createdProperties);

        $createStatements = SchemaBuilder::create($this->getDefinition(), $entities);

        foreach ($createStatements as $createStatement) {
            $this->connection->prepare($createStatement);
        }

        $this->processEntities($entities, false, $createdProperties);
    }

    /**
     * @param EntityCollection<TElement>|TElement|array $entities
     * @return void
     * @throws UnableToFindMatchingFkFieldForRelatedFieldException
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

        $changedProperties = $this->processEntities($entities, true, $changedProperties);

        $updateStatements = SchemaBuilder::update($this->getDefinition(), $entities, $changedProperties);

        foreach ($updateStatements as $updateStatement) {
            $this->connection->prepare($updateStatement);
        }

        $this->processEntities($entities, false, $changedProperties);
    }

    /**
     * Check for Relations to be saved. (ManyToMany, ManyToOne, OneToOne, OneToMany)
     * @param EntityCollection $entityCollection
     * @return array|null
     */
    private function processEntities(
        EntityCollection $entityCollection,
        bool $before,
        ?array $properties = null
    ): ?array {
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
                        // handled only in before
                        if (!$before) break;

                        // find the matching ForeignKeyField
                        $foreignKeyField = array_values(array_filter(
                            $this->getDefinition()::getDefinitionFields(),
                            fn($field) => $field instanceof FkField &&
                                $field->getStorageName() == $relationField->getStorageName()
                        ));

                        if(count($foreignKeyField) == 0) {
                            throw new UnableToFindMatchingFkFieldForRelatedFieldException(
                                $relationField->getStorageName(),
                                $this->getDefinition()
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
                                    $relationValue[$relationField->getRelatedTo()]
                                );

                            // add foreignkey field property to changed properties for update call.
                            $properties[$index][
                                $foreignKeyField->getInternalName()
                            ] = $relationValue[$relationField->getRelatedTo()];
                        }

                        break;
                    case ManyToManyRelation::class:
                        // handled only in after
                        if ($before) break;

                        // upsert the related values for example it upserts the roles when upserting account
                        $relatedRepository->upsert($relationValue);

                        $id = $properties[$index][$relationField->getRelatedBy()];
                        $relatedIds = array_map(
                            fn($entityJson) => $entityJson[$relationField->getRelatedTo()],
                            $relationValue
                        );

                        $existingIdsStatement = SchemaBuilder::selectExistingManyToManyDatasets(
                            $this->getDefinition(),
                            $relationField,
                            $id
                        );

                        $existingIdsResult = $this->connection->prepare($existingIdsStatement);
                        $existingIds = array_map(
                            fn($id) => $id[$relationField->getRelatedToDefinition()::getEntityName() . '_id'],
                            $existingIdsResult
                        );

                        $toBeDeleted = array_diff($existingIds, $relatedIds);
                        if(!empty($toBeDeleted)) {
                            $deleteManyToManyDatasetsStatement = SchemaBuilder::deleteDeletedManyToManyDatasets(
                                $this->getDefinition(),
                                $relationField,
                                $id,
                                $toBeDeleted
                            );

                            $this->connection->prepare($deleteManyToManyDatasetsStatement);
                        }

                        $toBeWritten = array_diff($relatedIds, $existingIds);
                        if(!empty($toBeWritten)) {
                            $writeManyToManyDatasetsStatement = SchemaBuilder::writeManyToManyDatasets(
                                $this->getDefinition(),
                                $relationField,
                                $id,
                                $toBeWritten
                            );

                            $result = $this->connection->prepare($writeManyToManyDatasetsStatement);
                        }

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
        $deleteQuery = SchemaBuilder::delete($this->getDefinition(), $criteria);

        $this->connection->prepare($deleteQuery);
    }

    /**
     * @param Criteria $criteria
     * @return int
     */
    public function count(Criteria $criteria): int
    {
        $countQuery = SchemaBuilder::count($this->getDefinition(), $criteria);

        $result = $this->connection->prepare($countQuery);

        return array_values($result[0])[0];
    }

    /**
     * @param Criteria $criteria
     * @param bool $preventAutoloadAssociations
     * @param int $depth
     * @return EntityCollection<TElement>
     */
    public function search(
        Criteria $criteria,
        bool $preventAutoloadAssociations = false,
        int $depth = -1
    ): EntityCollection {
        $statement = SchemaBuilder::search($this->getDefinition(), $criteria);

        $statementResult = $this->connection->prepare($statement);

        $statementResult = $this->loadAssociations(
            $criteria->getAssociations(),
            $statementResult,
            $preventAutoloadAssociations,
            $depth
        );

        return $this->buildEntityCollection($statementResult);
    }

    /**
     * @param Criteria $criteria
     * @return array
     */
    public function searchArrayResult(Criteria $criteria): array
    {
        $statement = SchemaBuilder::search($this->getDefinition(), $criteria);

        $statementResult = $this->connection->prepare($statement);

        return $this->loadAssociations($criteria->getAssociations(), $statementResult);
    }

    private function loadAssociations(
        array $associations,
        ?array $result,
        bool $preventAutoloadAssociations = false,
        int $depth = -1
    ): ?array {
        if(!$result) return [];

        $fields = array_filter(
            $this->getDefinition()::getDefinitionFields(),
            fn($field) => $field instanceof RelationField
        );

        for ($i = 0; $i < count($result); $i++) {
            /** @var RelationField $field */
            foreach ($fields as $field) {
                if(!$this->shouldLoadAssociation(
                    $field,
                    $associations,
                    $preventAutoloadAssociations,
                    $depth + 1
                )) continue;

                /** @var EntityRepository $relatedRepository */
                $relatedRepository = $this->container->get(
                    $field->getRelatedToDefinition()::getEntityName() . '.repository'
                );

                switch (get_class($field)) {
                    case ManyToOneRelation::class:
                    case OneToOneRelation::class:
                        if(!array_key_exists($field->getStorageName(), $result[$i])) break;

                        $relatedToInternalName = $this->convertRelatedToToInternalName($field);

                        $criteria = new Criteria();
                        $criteria->addAssociations(
                            ...$this->getRemainingAssociations(
                                $field,
                                $associations,
                                $depth + 1
                            )
                        );
                        $criteria->addFilter(new EqualsFilter(
                            $relatedToInternalName,
                            $result[$i][$field->getStorageName()]
                        ));

                        $result[$i][$field->getInternalName()] = $relatedRepository->search(
                            $criteria,
                            false,
                            $depth + 1
                        );
                        break;
                    case ManyToManyRelation::class:
                        if(!array_key_exists($field->getRelatedBy(), $result[$i])) break;

                        $existingIdsStatement = SchemaBuilder::selectExistingManyToManyDatasets(
                            $this->getDefinition(),
                            $field,
                            $result[$i][$field->getRelatedBy()]
                        );

                        $existingIdsResult = $this->connection->prepare($existingIdsStatement);
                        if(count($existingIdsResult) == 0) break;

                        $existingIds = array_map(
                            fn($a) => $a[$field->getRelatedToDefinition()::getEntityName() . '_id'],
                            $existingIdsResult
                        );

                        $relatedToInternalName = $this->convertRelatedToToInternalName($field);

                        $criteria = new Criteria();
                        $criteria->addAssociations(
                            ...$this->getRemainingAssociations(
                                $field,
                                $associations,
                                $depth + 1
                            )
                        );
                        $criteria->addFilter(new EqualsAnyFilter(
                            $relatedToInternalName,
                            $existingIds
                        ));

                        $result[$i][$field->getInternalName()] = $relatedRepository->search(
                            $criteria,
                            false,
                            $depth + 1
                        );
                        break;
                    case OneToManyRelation::class:
                        if(!array_key_exists($field->getRelatedBy(), $result[$i])) break;

                        $relatedToInternalName = $this->convertRelatedToToInternalName($field);

                        $criteria = new Criteria();
                        $criteria->addAssociations(
                            ...$this->getRemainingAssociations(
                                $field,
                                $associations,
                                $depth + 1
                            )
                        );
                        $criteria->addFilter(new EqualsFilter(
                            $relatedToInternalName,
                            $result[$i][$field->getRelatedBy()]
                        ));

                        $result[$i][$field->getInternalName()] = $relatedRepository->search(
                            $criteria,
                            true,
                            $depth + 1
                        );
                        break;
                }
            }
        }

        return $result;
    }

    private function convertRelatedToToInternalName(RelationField $field)
    {
        $filtered = SchemaBuilder::filterFieldsByStorageName($field->getRelatedToDefinition(), $field->getRelatedTo());
        if(count($filtered) == 0) return null;

        /** @var array<RelationField> $filtered */
        return $filtered[0]->getInternalName();
    }

    private function shouldLoadAssociation(
        RelationField $field,
        array $associations,
        bool $preventAutoloadAssociations,
        int $depth = -1
    ): bool {
        $shouldLoad = false;

        foreach ($associations as $association) {
            $explodedAssociation = explode('.', $association);

            if (count($explodedAssociation) <= $depth) continue;

            if ($explodedAssociation[$depth] == $field->getInternalName()) {
                $shouldLoad = true;
            }
        }

        if (!$shouldLoad && (!$field->isAutoload() || $preventAutoloadAssociations)) return false;

        return true;
    }

    private function getRemainingAssociations(
        RelationField $field,
        array $associations,
        int $depth
    ): array {
        $remainingAssociations = [];

        foreach ($associations as $association) {
            $explodedAssociation = explode('.', $association);

            if (count($explodedAssociation) <= $depth) continue;

            $associationDepth = $explodedAssociation[$depth];

            if($associationDepth != $field->getInternalName()) continue;

            $remainingAssociations[] = $association;
        }

        return $remainingAssociations;
    }

    /**
     * @param Criteria $criteria
     * @return array|null
     */
    public function searchIds(Criteria $criteria): array|null
    {
        $statement = SchemaBuilder::searchIds($this->getDefinition(), $criteria);

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
            try {
                $entities->add($this->buildEntity($entityDataSet, $ignoreErrors));
            } catch (
                PropertyNotFoundInEntityException|
            RequiredPropertyNotFoundInEntityDataSetException|
            \ReflectionException $ignore) {}
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
        $entityClass = $this->getDefinition()::getEntityClass();
        $fields = $this->getDefinition()::getDefinitionFields();

        /** @var Entity $entity */
        $entity = new $entityClass();
        $reflectionEntity = new ReflectionClass($entityClass);

        /** @var Field $field */
        foreach ($fields as $field) {
            if($field instanceof RelationField) {
                $internalName = $field->getInternalName();

                if (!array_key_exists($internalName, $entityDataSet)) {
                    continue;
                }

                $value = $entityDataSet[$internalName];
                if (!($value instanceof EntityCollection)) continue;

                if (!$reflectionEntity->hasProperty($internalName)) {
                    if (!$ignoreErrors) {
                        throw new PropertyNotFoundInEntityException($internalName, $entityClass);
                    }
                    break;
                }

                $property = $reflectionEntity->getProperty($internalName);

                switch (get_class($field)) {
                    case OneToOneRelation::class:
                    case ManyToOneRelation::class:
                        $property->setValue($entity, $value->first());
                        break;
                    case ManyToManyRelation::class:
                    case OneToManyRelation::class:
                        $property->setValue($entity, $value);
                        break;
                }

                continue;
            }

            // determine if the field is required or not.
            $required = ($field->hasFlag(Required::class) ||
                    $field->hasFlag(PrimaryKey::class)) &&
                !$field->hasFlag(AutoIncrement::class);

            // get storageName by storageName or internalName if the dataset is entered using upsert.
            $storageName = $this->getStorageName($field, $entityDataSet);
            $internalName = $field->getInternalName();

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

        if(array_key_exists($field->getStorageName(), $entityDataSet)) {
            $storageName = $field->getStorageName();
        }else if(array_key_exists($field->getInternalName(), $entityDataSet)) {
            $storageName = $field->getInternalName();
        }

        return $storageName;
    }

    /**
     * @return string
     */
    public function getDefinition(): string
    {
        return $this->definition;
    }

}