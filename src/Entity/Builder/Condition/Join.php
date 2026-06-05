<?php

namespace NewDavis\DatabaseManagement\Entity\Builder\Condition;

use NewDavis\DatabaseManagement\Entity\EntityDefinitionInterface;
use NewDavis\DatabaseManagement\Entity\Field\Relational\ManyToManyRelation;
use NewDavis\DatabaseManagement\Entity\Field\Relational\ManyToOneRelation;
use NewDavis\DatabaseManagement\Entity\Field\Relational\OneToManyRelation;
use NewDavis\DatabaseManagement\Entity\Field\Relational\OneToOneRelation;

class Join
{
    public function __construct(
        private readonly EntityDefinitionInterface $definition,
        private readonly ManyToManyRelation|OneToManyRelation|ManyToOneRelation|OneToOneRelation $relationalField,
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
     * @return ManyToManyRelation|OneToManyRelation|ManyToOneRelation|OneToOneRelation
     */
    public function getRelationalField(): ManyToManyRelation|OneToManyRelation|ManyToOneRelation|OneToOneRelation
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