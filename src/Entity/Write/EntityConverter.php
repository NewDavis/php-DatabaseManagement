<?php

namespace NewDavis\DatabaseManagement\Entity\Write;

use NewDavis\DatabaseManagement\Entity\AbstractEntity;
use NewDavis\DatabaseManagement\Entity\AbstractEntityCollection;
use NewDavis\DatabaseManagement\Entity\EntityDefinitionInterface;
use NewDavis\DatabaseManagement\Entity\EntityRegistry;
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

        foreach ($entityData as $entityDataSet) {
            try {
                $entity = $this->convertArrayToEntity($definition, $entityDataSet);
            }catch (\Exception $e) {
                dd($e);
            }

            $collection->add($entity);
        }

        return $collection;
    }

    public function convertArrayToEntity(
        EntityDefinitionInterface $definition,
        array $entityData
    ): AbstractEntity {
        $entity = EntityHelper::createEmptyEntity($definition);

        if ($entity == null) {
            throw new CouldNotCreateEntityInstanceException($definition);
        }

        /** @var RelationalField $relationalField */
        foreach ($definition->getFields()->filter(RelationalField::class) as $relationalField) {
            if (!array_key_exists($relationalField->getInternalName(), $entityData)) continue;

            $relatedDefinition = $this->registry->getDefinitionByDefinitionClass($relationalField->getRelatedToDefinition());
            $relationalData = $entityData[$relationalField->getInternalName()];

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
                $relationalField->getInternalName(),
                $relationalField->getSerializer()->encode($relatedValue)
            );
        }

        /** @var ScalarField $scalarField */
        foreach ($definition->getFields()->filter(ScalarField::class) as $scalarField) {
            if (!array_key_exists($scalarField->getInternalName(), $entityData)) continue;

            $propertyData = $entityData[$scalarField->getInternalName()];

            $entity->set(
                $scalarField->getInternalName(),
                $scalarField->getSerializer()->encode($propertyData)
            );
        }

        return $entity;
    }
}
