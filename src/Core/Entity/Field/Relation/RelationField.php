<?php

namespace NewDavis\DatabaseManagement\Core\Entity\Field\Relation;

abstract class RelationField
{

    public function __construct(
        private readonly string $internalName,
        private readonly ?string $storageName,
        private readonly string $relatedToDefinition,
        private readonly ?string $relatedByInternalName = 'id',
        private readonly string $relatedToInternalName = 'id',
        private readonly bool $autoload = false
    ) {}

    /**
     * @return string
     */
    public function getInternalName(): string
    {
        return $this->internalName;
    }

    /**
     * @return string|null
     */
    public function getStorageName(): ?string
    {
        return $this->storageName;
    }

    /**
     * @return string
     */
    public function getRelatedToDefinition(): string
    {
        return $this->relatedToDefinition;
    }

    /**
     * @return string|null
     */
    public function getRelatedByInternalName(): ?string
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

    /**
     * @return bool
     */
    public function isAutoload(): bool
    {
        return $this->autoload;
    }

}