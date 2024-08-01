<?php

namespace NewDavis\DatabaseManagement;

use NewDavis\DatabaseManagement\Core\DatabaseManagementExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class DatabaseManagementBundle extends AbstractBundle
{

    public function getContainerExtension(): ?ExtensionInterface
    {
        return new DatabaseManagementExtension();
    }

    public function getPath(): string
    {
        return dirname(__DIR__);
    }

}