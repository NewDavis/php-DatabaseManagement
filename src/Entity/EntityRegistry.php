<?php

namespace NewDavis\DatabaseManagement\Entity;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class EntityRegistry
{
    public const REPOSITORY_SUFFIX = '.repository';

    private array $definitions = [];

    public function createRepository(EntityDefinitionInterface $entityDefinition, ContainerBuilder $container): EntityRepository
    {
        $entityName = $entityDefinition->getEntityName();

        $repository = new EntityRepository($entityDefinition);

        $definition = new Definition(EntityRepository::class);
        $definition->setArguments([
            $entityDefinition
        ]);

        $container->set($entityName . self::REPOSITORY_SUFFIX, $definition);

        return $repository;
    }

    public function register(EntityDefinitionInterface $definition, EntityRepository $entityRepository): void
    {
        $this->definitions[$definition::class] = [
            'instance' => $definition,
            'repository' => $entityRepository
        ];
    }
}