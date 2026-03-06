<?php

namespace NewDavis\DatabaseManagement\Entity;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class EntityRegistryCompilerPass implements CompilerPassInterface
{
    private ?EntityRegistry $entityRegistry = null;

    public function process(ContainerBuilder $container)
    {
        if (!$this->searchRegistry($container)) return;

        $definitions = array_keys($container->findTaggedServiceIds('newdavis.entity.definition'));

        foreach ($definitions as $definitionId) {
            $definition = $container->get($definitionId);

            if (!($definition instanceof EntityDefinitionInterface)) continue;

            $repository = $this->entityRegistry->createRepository($definition, $container);

            $this->entityRegistry->register($definition, $repository);
        }
    }

    private function searchRegistry(ContainerBuilder $container): ?EntityRegistry
    {
        return $this->entityRegistry = $container->get(EntityRegistry::class);
    }
}