<?php

namespace NewDavis\DatabaseManagement\Entity\Field\Relational;

use NewDavis\DatabaseManagement\Entity\Field\StorableInterface;

class ManyToOneRelation extends RelationalField implements StorableInterface
{
    public function __construct(
        string $internalName,
        private readonly string $storageName,
        string $relatedToDefinition,
        string $relatedToInternalName,
        bool $autoLoad = false
    ) {
        parent::__construct(
            $internalName,
            $relatedToDefinition,
            $relatedToInternalName,
            $autoLoad
        );
    }

    public function getStorageName(): string
    {
        return $this->storageName;
    }
}