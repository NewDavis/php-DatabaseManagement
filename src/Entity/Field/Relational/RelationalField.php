<?php

namespace NewDavis\DatabaseManagement\Entity\Field\Relational;

use NewDavis\DatabaseManagement\Entity\Field\Field;
use NewDavis\DatabaseManagement\Entity\Field\FieldInterface;

abstract class RelationalField extends Field implements RelationalFieldInterface, FieldInterface
{
    /**
     * @param string $internalName
     * @param string $relatedToDefinition
     * @param string $relatedToInternalName
     * @param bool $autoload
     */
    public function __construct(
        private readonly string $internalName,
        private readonly string $relatedToDefinition,
        private readonly string $relatedToInternalName,
        private readonly bool $autoload
    ) {
        parent::__construct($this->internalName);
    }

    /**
     * @return string
     */
    public function getInternalName(): string
    {
        return $this->internalName;
    }

    /**
     * @return string
     */
    public function getRelatedToDefinition(): string
    {
        return $this->relatedToDefinition;
    }

    /**
     * @return string
     */
    public function getRelatedToInternalName(): string
    {
        return $this->relatedToInternalName;
    }

    public function shouldAutoLoad(): bool
    {
        return $this->autoload;
    }
}