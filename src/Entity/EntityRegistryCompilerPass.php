<?php

namespace NewDavis\DatabaseManagement\Entity;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class EntityRegistryCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->has(EntityRegistry::class)) {
            return;
        }

        $registryDefinition = $container->findDefinition(EntityRegistry::class);

        $definitions = $container->findTaggedServiceIds('newdavis.entity.definition');

        foreach ($definitions as $serviceId => $tags) {

            $entityDefinition = $container->get($serviceId);
            $definition = $container->getDefinition($serviceId);
            $class = $definition->getClass();

            if (!$class || !is_subclass_of($class, EntityDefinitionInterface::class)) {
                continue;
            }

            $entityName = $entityDefinition->getEntityName();
            $repositoryId = $entityName . '.repository';

            $repositoryDefinition = new Definition(EntityRepository::class);
            $repositoryDefinition->setArguments([
                new Reference($serviceId),
                new Reference(EntityRegistry::class)
            ]);

            $container->setDefinition($repositoryId, $repositoryDefinition);

            $registryDefinition->addMethodCall(
                'register',
                [
                    new Reference($serviceId),
                    new Reference($repositoryId)
                ]
            );
        }
    }
}
