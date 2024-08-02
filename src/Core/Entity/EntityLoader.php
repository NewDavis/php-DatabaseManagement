<?php

namespace NewDavis\DatabaseManagement\Core\Entity;

use NewDavis\DatabaseManagement\Core\Entity\Property\Property;
use NewDavis\DatabaseManagement\Core\Entity\Property\Relation\RelationProperty;
use Symfony\Component\DependencyInjection\Container;

class EntityLoader
{

    public const REPOSITORY_EXTENSION = '.repository';
    public const SCHEMA_EXTENSION = '.schema';

    public function __construct(private readonly Container $container)
    {}

    public function getEntityRepositoryByEntityName(string $entityName) : EntityRepository|null
    {
        return $this->container->get($entityName . self::REPOSITORY_EXTENSION);
    }

    public function getRelatedEntityNameByPropertyName(EntityRepository $entityRepository, string $propertyName) : string|null
    {
        $property = $this->getPropertyByName($entityRepository, $propertyName);

        if(!($property instanceof RelationProperty)) return null;

        return $property->getReferencedEntity();
    }

    public function getPropertyByName(EntityRepository $entityRepository, string $propertyName) : RelationProperty|null
    {
        foreach ($entityRepository->getEntityDefinition()->getPropertyDefinition() as $property) {


            if($property->getProperty() === $propertyName) return $property;
        }

        return null;
    }

}