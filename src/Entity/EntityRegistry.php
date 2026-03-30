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

    public function __construct(
        #[Autowire(service: Connection::class)] private readonly Connection $connection
    ) {
        $this->converter = new EntityConverter($this);
    }

    public function register(EntityDefinitionInterface $definition, EntityRepository $repository): void
    {
        $this->definitions[$definition::class] = $definition;
        $this->repositories[$definition::class] = $repository;
    }

    public function getDefinitionByDefinitionClass(string $definitionClass): ?EntityDefinitionInterface
    {
        if (!array_key_exists($definitionClass, $this->definitions)) {
            return null;
        }

        return $this->definitions[$definitionClass];
    }

    public function getRepositoryByDefinitionClass(string $definitionClass): ?EntityRepositoryInterface
    {
        if (!array_key_exists($definitionClass, $this->repositories)) {
            return null;
        }

        return $this->repositories[$definitionClass];
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
