<?php

namespace NewDavis\DatabaseManagement\Entity\Field\Relational;

class ManyToManyRelation extends RelationalField implements RelatedByInterface
{
    public function __construct(
        string $internalName,
        string $relatedToDefinition,
        private readonly string $relatedByInternalName,
        string $relatedToInternalName,
        private readonly ?string $mappingTableName = null,
        bool $autoLoad = false
    ) {
        parent::__construct(
            $internalName,
            $relatedToDefinition,
            $relatedToInternalName,
            $autoLoad
        );
    }

    public function getRelatedByInternalName(): string
    {
        return $this->relatedByInternalName;
    }

    /**
     * @return string|null
     */
    public function getMappingTableName(): ?string
    {
        return $this->mappingTableName;
    }
}