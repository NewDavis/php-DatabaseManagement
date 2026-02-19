<?php

namespace NewDavis\DatabaseManagement\Entity\Field\Relational;

class OneToOneRelation extends RelationalField
{
    public function __construct(string $internalName, string $storageName, string $relatedToDefinition, ?string $relatedByInternalName, string $relatedToInternalName, bool $autoLoad, array ...$flags)
    {
        parent::__construct($internalName, $storageName, $relatedToDefinition, $relatedByInternalName, $relatedToInternalName, $autoLoad, $flags);
    }
}