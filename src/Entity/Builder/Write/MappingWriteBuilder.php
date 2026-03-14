<?php

namespace NewDavis\DatabaseManagement\Entity\Builder\Write;

use NewDavis\DatabaseManagement\Entity\AbstractEntityCollection;
use NewDavis\DatabaseManagement\Entity\EntityDefinitionInterface;
use NewDavis\DatabaseManagement\Entity\EntityRegistry;
use NewDavis\DatabaseManagement\Entity\Field\Relational\ManyToManyRelation;
use NewDavis\DatabaseManagement\Entity\Write\EntityWriteStatement;

class MappingWriteBuilder
{
    public function __construct(
        private readonly EntityRegistry $registry,
        private readonly EntityDefinitionInterface $definition
    ) {
    }

    public function build(
        ManyToManyRelation $manyToManyRelation,
        AbstractEntityCollection $collection
    ): EntityWriteStatement {
        $query = <<<SQL

SQL;

        return new EntityWriteStatement($query, []);
    }
}