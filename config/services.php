<?php

declare(strict_types=1);

use DatabaseManagement\Core\Driver\Connection;
use DatabaseManagement\Core\Entity\EntityRepository;
use DatabaseManagement\Entity\Account\AccountDefinition;
use DatabaseManagement\Entity\Role\RoleDefinition;
use DatabaseManagement\Entity\Upload\UploadDefinition;
use DatabaseManagement\EventListener\KernelTerminateListener;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $containerConfigurator): void {
    $definitionExtension = '.definition';
    $repositoryExtension = '.repository';

    $services = $containerConfigurator->services();

    $services->set(Connection::class, Connection::class)
        ->arg('$container', service('service_container'));

    /*$services->set(EntityLoader::class, EntityLoader::class)
        ->arg('$container', service('service_container'))
        ->arg('$projectRoot', '%kernel.project_dir%');*/

    $services->set(KernelTerminateListener::class, KernelTerminateListener::class)
        ->arg('$connection', service(Connection::class))
        ->tag('kernel.event_subscriber');

    /*$services->set(AccountDefinition::ENTITY_NAME . $definitionExtension, AccountDefinition::class);
    $services->set(AccountDefinition::ENTITY_NAME . $repositoryExtension, EntityRepository::class)
        ->public()
        ->arg('$entityDefinition', service(AccountDefinition::ENTITY_NAME . $definitionExtension))
        ->arg('$connection', service(Connection::class))
        ->arg('$container', service('service_container'));

    $services->set(RoleDefinition::ENTITY_NAME . $definitionExtension, RoleDefinition::class);
    $services->set(RoleDefinition::ENTITY_NAME . $repositoryExtension, EntityRepository::class)
        ->public()
        ->arg('$entityDefinition', service(RoleDefinition::ENTITY_NAME . $definitionExtension))
        ->arg('$connection', service(Connection::class))
        ->arg('$container', service('service_container'));

    $services->set(UploadDefinition::ENTITY_NAME . $definitionExtension, UploadDefinition::class);
    $services->set(UploadDefinition::ENTITY_NAME . $repositoryExtension, EntityRepository::class)
        ->public()
        ->arg('$entityDefinition', service(UploadDefinition::ENTITY_NAME . $definitionExtension))
        ->arg('$connection', service(Connection::class))
        ->arg('$container', service('service_container'));*/
};
