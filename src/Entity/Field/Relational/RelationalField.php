<?php

namespace NewDavis\DatabaseManagement\Entity\Field\Relational;

use NewDavis\DatabaseManagement\Entity\Field\FieldInterface;
use NewDavis\DatabaseManagement\Entity\Field\Flag\Flag;

abstract class RelationalField implements RelationalFieldInterface, FieldInterface
{
    /** @var Flag[] */
    private readonly array $flags;

    public function __construct(
        private readonly string $internalName,
        private readonly string $relatedToDefinition,
        private readonly string $relatedByInternalName,
        private readonly string $relatedToInternalName,
        private readonly bool $autoload,
        array ...$flags
    ) {
        $this->flags = $flags;
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
    public function getRelatedByInternalName(): string
    {
        return $this->relatedByInternalName;
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

    /**
     * @return Flag[]
     */
    public function getFlags(): array
    {
        return $this->flags;
    }
}