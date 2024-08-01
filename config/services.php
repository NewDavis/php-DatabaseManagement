<?php

declare(strict_types=1);

use NewDavis\DatabaseManagement\Core\Driver\Connection;
use NewDavis\DatabaseManagement\Core\Entity\EntityLoader;
use NewDavis\DatabaseManagement\EventListener\KernelTerminateListener;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(Connection::class, Connection::class)
        ->arg('$container', service('service_container'));

    $services->set(EntityLoader::class, EntityLoader::class)
        ->arg('$container', service('service_container'));

    $services->set(KernelTerminateListener::class, KernelTerminateListener::class)
        ->arg('$connection', service(Connection::class))
        ->tag('kernel.event_subscriber');
};
