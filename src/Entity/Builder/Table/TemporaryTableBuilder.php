<?php

namespace NewDavis\DatabaseManagement\Entity\Builder\Table;

use NewDavis\DatabaseManagement\Entity\Write\EntityWriteStatement;

class TemporaryTableBuilder
{
    public function __construct(
        private readonly TableBuilder $table,
    ) {
    }

    public function create(): EntityWriteStatement
    {
        return new EntityWriteStatement(
            $this->table->build(true),
            []
        );
    }

    public function truncate(): EntityWriteStatement
    {
        return new EntityWriteStatement(
            <<<SQL
DELETE FROM `tmp_{$this->table->getTableName()}`
SQL,
            []
        );
    }
}