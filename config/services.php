<?php

declare(strict_types=1);

use NewDavis\DatabaseManagement\Controller\TestController;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()->public()->autowire()->autoconfigure();

    $services->set(TestController::class)
        ->tag('controller.service_arguments');
};
