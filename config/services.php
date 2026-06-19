<?php

declare(strict_types=1);

use NewDavis\DatabaseManagement\Command\DatabaseCreateTablesCommand;
use NewDavis\DatabaseManagement\Connection;
use NewDavis\DatabaseManagement\Controller\TestController;
use NewDavis\DatabaseManagement\Demo\Account\AccountDefinition;
use NewDavis\DatabaseManagement\Demo\Role\RoleDefinition;
use NewDavis\DatabaseManagement\Demo\Token\TokenDefinition;
use NewDavis\DatabaseManagement\Entity\EntityRegistry;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()->public()->autowire()->autoconfigure();

    $services->set(Connection::class);

    $services->set(DatabaseCreateTablesCommand::class)
        ->tag('console.command');

    /*$services->set(TestController::class)
        ->tag('controller.service_arguments');

    $services->set(AccountDefinition::class)
        ->tag('newdavis.entity.definition');

    $services->set(RoleDefinition::class)
        ->tag('newdavis.entity.definition');

    $services->set(TokenDefinition::class)
        ->tag('newdavis.entity.definition');*/

    $services->set(EntityRegistry::class);
};
