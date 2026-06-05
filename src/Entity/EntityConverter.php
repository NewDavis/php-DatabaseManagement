<?php

namespace NewDavis\DatabaseManagement\Entity;

use NewDavis\DatabaseManagement\Entity\Exception\Write\CouldNotCreateEntityInstanceException;
use NewDavis\DatabaseManagement\Entity\Field\Relational\ManyToManyRelation;
use NewDavis\DatabaseManagement\Entity\Field\Relational\ManyToOneRelation;
use NewDavis\DatabaseManagement\Entity\Field\Relational\OneToManyRelation;
use NewDavis\DatabaseManagement\Entity\Field\Relational\OneToOneRelation;
use NewDavis\DatabaseManagement\Entity\Field\Relational\RelationalField;
use NewDavis\DatabaseManagement\Entity\Field\Scalar\ScalarField;
use NewDavis\DatabaseManagement\Util\Helper\EntityHelper;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class EntityConverter
{
    public function __construct(
        #[Autowire(service: EntityRegistry::class)] private readonly EntityRegistry $registry
    ) {
    }

    public function convertArrayToEntityCollection(
        EntityDefinitionInterface $definition,
        array $entityData
    ): AbstractEntityCollection {
        $collection = EntityHelper::createCollection($definition);

        $mapping = null;
        foreach ($entityData as $entityDataSet) {
            if ($mapping == null) {
                $mapping = $this->createPropertyMapping($definition, $entityDataSet);
            }

            try {
                $entity = $this->convertArrayToEntity($definition, $entityDataSet, $mapping);
            }catch (\Exception $e) {
                dd($e);
            }

            $collection->add($entity);
        }

        return $collection;
    }

    private function createPropertyMapping(
        EntityDefinitionInterface $definition,
        array $entityData
    ): array {
        $mapping = [];

        foreach (array_keys($entityData) as $key) {
            try {
                $fieldByStorageName = $definition->getFields()->getByStorageName($key);

                if ($fieldByStorageName != null) {
                    $mapping[$fieldByStorageName->getInternalName()] = $key;
                    continue;
                }

                $fieldByInternalName = $definition->getFields()->getByInternalName($key);

                if ($fieldByInternalName !== null) {
                    $mapping[$fieldByInternalName->getInternalName()] = $key;
                    continue;
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        return $mapping;
    }

    public function convertArrayToEntity(
        EntityDefinitionInterface $definition,
        array $entityData,
        ?array $mapping = null
    ): AbstractEntity {
        $entity = EntityHelper::createEmptyEntity($definition);

        if ($entity == null) {
            throw new CouldNotCreateEntityInstanceException($definition);
        }

        /** @var RelationalField $relationalField */
        foreach ($definition->getFields()->filter(RelationalField::class) as $relationalField) {
            $key = $mapping[$relationalField->getInternalName()] ?? $relationalField->getInternalName();

            if (!array_key_exists($key, $entityData)) continue;

            $relatedDefinition = $this->registry->getDefinitionByDefinitionClass($relationalField->getRelatedToDefinition());
            $relationalData = $entityData[$key];

            $relatedValue = null;
            switch (get_class($relationalField)) {
                case ManyToOneRelation::class:
                case OneToOneRelation::class:
                    $relatedValue = $this->convertArrayToEntity($relatedDefinition, $relationalData);
                    break;
                case OneToManyRelation::class:
                case ManyToManyRelation::class:
                    $relatedValue = $this->convertArrayToEntityCollection($relatedDefinition, $relationalData);
                    break;
            }

            $entity->set(
                $relationalField,
                $relationalField->getInternalName(),
                $relatedValue
            );
        }

        /** @var ScalarField $scalarField */
        foreach ($definition->getFields()->filter(ScalarField::class) as $scalarField) {
            $key = $mapping[$scalarField->getInternalName()] ?? $scalarField->getInternalName();

            if (!array_key_exists($key, $entityData)) continue;

            $propertyData = $entityData[$key];

            $entity->set(
                $scalarField,
                $scalarField->getInternalName(),
                $propertyData
            );
        }

        return $entity;
    }
}
