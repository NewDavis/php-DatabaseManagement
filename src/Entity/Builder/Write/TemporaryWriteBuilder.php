<?php

namespace NewDavis\DatabaseManagement\Entity\Builder\Write;

use NewDavis\DatabaseManagement\Entity\EntityDefinitionInterface;
use NewDavis\DatabaseManagement\Entity\EntityRegistry;
use NewDavis\DatabaseManagement\Entity\Write\EntityWriteStatementCollection;

class TemporaryWriteBuilder
{
    public function __construct(
        private readonly EntityRegistry $registry,
        private readonly EntityDefinitionInterface $definition
    ) {
    }

    public function writeInTemporaryTable(array $raw): EntityWriteStatementCollection
    {
        return new EntityWriteStatementCollection([]);
    }
}