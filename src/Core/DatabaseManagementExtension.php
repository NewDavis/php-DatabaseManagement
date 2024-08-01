<?php

namespace NewDavis\DatabaseManagement\Core;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

class DatabaseManagementExtension extends Extension
{

    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new PhpFileLoader($container, new FileLocator(__DIR__ . '/../../config'));
        $loader->load('services.php');
    }

}