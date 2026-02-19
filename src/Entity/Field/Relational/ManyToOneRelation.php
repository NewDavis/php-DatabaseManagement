<?php

namespace NewDavis\DatabaseManagement\Entity\Field\Relational;

class ManyToOneRelation extends RelationalField
{
    public function __construct(
        string $internalName,
        private readonly string $storageName,
        string $relatedToDefinition,
        ?string $relatedByInternalName,
        string $relatedToInternalName,
        bool $autoLoad,
        array ...$flags
    ) {
        parent::__construct(
            $internalName,
            $relatedToDefinition,
            $relatedByInternalName,
            $relatedToInternalName,
            $autoLoad,
            $flags
        );
    }

    public function getStorageName(): string
    {
        return $this->storageName;
    }
}