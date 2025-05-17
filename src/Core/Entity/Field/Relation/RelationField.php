<?php

namespace NewDavis\DatabaseManagement\Core\Entity\Field\Relation;

abstract class RelationField
{

    public function __construct(
        private readonly string  $internalName,
        private readonly ?string $storageName,
        private readonly string  $relatedToDefinition,
        private readonly ?string $relatedBy = 'id',
        private readonly string  $relatedTo = 'id',
        private readonly bool    $autoload = false
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
    public function getRelatedBy(): ?string
    {
        return $this->relatedBy;
    }

    /**
     * @return string
     */
    public function getRelatedTo(): string
    {
        return $this->relatedTo;
    }

    /**
     * @return bool
     */
    public function isAutoload(): bool
    {
        return $this->autoload;
    }

}