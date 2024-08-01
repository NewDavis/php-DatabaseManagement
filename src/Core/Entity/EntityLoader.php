<?php

namespace NewDavis\DatabaseManagement\Core\Entity;

use Symfony\Component\DependencyInjection\Container;

class EntityLoader
{

    public const REPOSITORY_EXTENSION = '.repository';
    public const SCHEMA_EXTENSION = '.schema';

    public function __construct(private readonly Container $container)
    {}

    public function getEntityRepositoryByEntityName(string $entityName) : EntityRepository|null
    {
        return $this->container->get($entityName . self::REPOSITORY_EXTENSION);
    }

}