<?php

namespace NewDavis\DatabaseManagement\Entity\Builder\Read;

use NewDavis\DatabaseManagement\Entity\EntityDefinitionInterface;
use NewDavis\DatabaseManagement\Entity\Field\Relational\RelationalField;

class Join
{
    public function __construct(
        private readonly EntityDefinitionInterface $definition,
        private readonly RelationalField $relationalField,
        private readonly EntityDefinitionInterface $relatedDefinition,
        private readonly string $alias
    ) {
    }

    /**
     * @return EntityDefinitionInterface
     */
    public function getDefinition(): EntityDefinitionInterface
    {
        return $this->definition;
    }

    /**
     * @return RelationalField
     */
    public function getRelationalField(): RelationalField
    {
        return $this->relationalField;
    }

    /**
     * @return EntityDefinitionInterface
     */
    public function getRelatedDefinition(): EntityDefinitionInterface
    {
        return $this->relatedDefinition;
    }

    /**
     * @return string
     */
    public function getAlias(): string
    {
        return $this->alias;
    }
}