<?php

declare(strict_types=1);

use NewDavis\DatabaseManagement\Controller\TestController;
use NewDavis\DatabaseManagement\Core\Driver\Connection;
use NewDavis\DatabaseManagement\Core\Entity\EntityRepository;
use NewDavis\DatabaseManagement\Test\Account\AccountDefinition;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()->public()->autowire()->autoconfigure();

    $services->set(TestController::class, TestController::class);

    $services->set(AccountDefinition::getEntityName() . '.repository', EntityRepository::class)
        ->arg('$definition', AccountDefinition::class);

    $services->set(Connection::class, Connection::class);
};
