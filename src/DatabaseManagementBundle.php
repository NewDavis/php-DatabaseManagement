<?php

namespace NewDavis\DatabaseManagement;

use NewDavis\DatabaseManagement\Entity\EntityRegistryCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class DatabaseManagementBundle extends AbstractBundle
{
    public const DEBUG = false;

    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(
            new EntityRegistryCompilerPass()
        );
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('../config/services.php');
    }

    public function getPath(): string
    {
        return dirname(__DIR__);
    }

}