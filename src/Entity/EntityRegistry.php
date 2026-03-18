<?php

namespace NewDavis\DatabaseManagement\Entity;

use NewDavis\DatabaseManagement\Connection;
use NewDavis\DatabaseManagement\Entity\Builder\Table\TableBuilder;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class EntityRegistry
{
    private EntityConverter $converter;
    private array $repositories = [];
    private array $definitions = [];
    private array $tableBuilders = [];

    public function __construct(
        #[Autowire(service: Connection::class)] private readonly Connection $connection
    ) {
        $this->converter = new EntityConverter($this);
    }

    public function register(EntityDefinitionInterface $definition, EntityRepository $repository): void
    {
        $this->definitions[$definition->getEntityName()] = $definition;
        $this->definitions[$definition::class] = $definition;
        $this->repositories[$definition->getEntityName()] = $repository;
        $this->repositories[$definition::class] = $repository;
        $this->tableBuilders[$definition::class] = TableBuilder::fromDefinition($this, $definition);
    }

    public function getDefinitionByEntityName(string $entityName): ?EntityDefinitionInterface
    {
        if (!array_key_exists($entityName, $this->definitions)) {
            return null;
        }

        return $this->definitions[$entityName];
    }

    public function getDefinitionByDefinitionClass(string $definitionClass): ?EntityDefinitionInterface
    {
        if (!array_key_exists($definitionClass, $this->definitions)) {
            return null;
        }

        return $this->definitions[$definitionClass];
    }

    public function getRepositoryByEntityName(string $entityName): ?EntityRepositoryInterface
    {
        if (!array_key_exists($entityName, $this->repositories)) {
            return null;
        }

        return $this->repositories[$entityName];
    }

    public function getRepositoryByDefinitionClass(string $definitionClass): ?EntityRepositoryInterface
    {
        if (!array_key_exists($definitionClass, $this->repositories)) {
            return null;
        }

        return $this->repositories[$definitionClass];
    }

    public function getTableBuilderByDefinitionClass(string $definitionClass): ?TableBuilder
    {
        if (!array_key_exists($definitionClass, $this->tableBuilders)) {
            return null;
        }

        return $this->tableBuilders[$definitionClass];
    }

    /**
     * @return Connection
     */
    public function getConnection(): Connection
    {
        return $this->connection;
    }

    /**
     * @return EntityConverter
     */
    public function getConverter(): EntityConverter
    {
        return $this->converter;
    }
}
