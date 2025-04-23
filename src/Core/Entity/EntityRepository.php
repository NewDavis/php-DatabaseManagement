<?php

namespace NewDavis\DatabaseManagement\Core\Entity;

use NewDavis\DatabaseManagement\Core\Driver\Connection;
use NewDavis\DatabaseManagement\Core\Schema\SchemaBuilder;
use NewDavis\DatabaseManagement\Core\Search\Criteria\Criteria;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class EntityRepository
{

    public function __construct(
        private readonly string $definition,
        #[Autowire(service: Connection::class)] private readonly Connection $connection
    ) {
    }

    public function upsert()
    {

    }

    public function create()
    {

    }

    public function update()
    {

    }

    public function delete()
    {

    }

    public function search(Criteria $criteria): EntityCollection
    {
        $searchQuery = SchemaBuilder::search($this->definition, $criteria);
        dd($searchQuery);
        return new EntityCollection();
    }

    public function searchIds(Criteria $criteria): EntityCollection
    {
        return new EntityCollection();
    }

}