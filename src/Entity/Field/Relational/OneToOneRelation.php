<?php

namespace NewDavis\DatabaseManagement\Entity\Field\Relational;

use NewDavis\DatabaseManagement\Entity\Field\StorableInterface;

class OneToOneRelation extends RelationalField implements StorableInterface
{
    public function __construct(
        string $internalName,
        private readonly string $storageName,
        string $relatedToDefinition,
        string $relatedByInternalName,
        string $relatedToInternalName,
        bool $autoLoad = false
    ) {
        parent::__construct(
            $internalName,
            $relatedToDefinition,
            $relatedByInternalName,
            $relatedToInternalName,
            $autoLoad
        );
    }

    public function getStorageName(): string
    {
        return $this->storageName;
    }
}