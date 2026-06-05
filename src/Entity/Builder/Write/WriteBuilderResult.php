<?php

namespace NewDavis\DatabaseManagement\Entity\Builder\Write;

use NewDavis\DatabaseManagement\Entity\Write\EntityWriteStatement;
use NewDavis\DatabaseManagement\Entity\Write\EntityWriteStatementCollection;

class WriteBuilderResult
{
    private readonly EntityWriteStatementCollection $relatedQueries;
    private readonly EntityWriteStatementCollection $mainQueries;
    private readonly EntityWriteStatementCollection $mappingQueries;

    public function __construct()
    {
        $this->relatedQueries = new EntityWriteStatementCollection([]);
        $this->mainQueries = new EntityWriteStatementCollection([]);
        $this->mappingQueries = new EntityWriteStatementCollection([]);
    }

    public function add(WriteBuilderStatementType $statementType, EntityWriteStatement $statement): void
    {
        switch ($statementType) {
            case WriteBuilderStatementType::RELATED:
                $this->relatedQueries->add($statement);
                break;
            case WriteBuilderStatementType::MAIN:
                $this->mainQueries->add($statement);
                break;
            case WriteBuilderStatementType::MAPPING:
                $this->mappingQueries->add($statement);
                break;
        }
    }

    /**
     * @param WriteBuilderStatementType $statementType
     * @param array<EntityWriteStatement> $statements
     * @return void
     */
    public function addAll(WriteBuilderStatementType $statementType, array $statements): void
    {
        foreach ($statements as $statement) {
            $this->add($statementType, $statement);
        }
    }

    /**
     * @return EntityWriteStatementCollection
     */
    public function getRelatedQueries(): EntityWriteStatementCollection
    {
        return $this->relatedQueries;
    }

    /**
     * @return EntityWriteStatementCollection
     */
    public function getMainQueries(): EntityWriteStatementCollection
    {
        return $this->mainQueries;
    }

    /**
     * @return EntityWriteStatementCollection
     */
    public function getMappingQueries(): EntityWriteStatementCollection
    {
        return $this->mappingQueries;
    }

    public function combineQueries(): EntityWriteStatementCollection
    {
        return new EntityWriteStatementCollection([
            ...$this->relatedQueries->getStatements(),
            ...$this->mainQueries->getStatements(),
            ...$this->mappingQueries->getStatements()
        ]);
    }
}