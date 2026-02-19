<?php

namespace NewDavis\DatabaseManagement\Entity\Field\Relational;

class ManyToManyRelation extends RelationalField
{
    public function __construct(
        string $internalName,
        string $relatedToDefinition,
        ?string $relatedByInternalName,
        string $relatedToInternalName,
        bool $autoLoad,
        array ...$flags
    ) {
        parent::__construct($internalName, $relatedToDefinition, $relatedByInternalName, $relatedToInternalName, $autoLoad, $flags);
    }
}