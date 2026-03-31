<?php

namespace NewDavis\DatabaseManagement\Entity\Builder\Table;

use NewDavis\DatabaseManagement\Entity\Write\EntityWriteStatement;
use NewDavis\DatabaseManagement\Entity\Write\EntityWriteStatementCollection;

class TemporaryTableBuilder
{
    public function __construct(
        private readonly TableBuilder $table,
    ) {
    }

    public function build(): EntityWriteStatementCollection
    {
        return new EntityWriteStatementCollection([
            new EntityWriteStatement(
                $this->table->build(true),
                []
            )
        ]);
    }
}