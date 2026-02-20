<?php

namespace NewDavis\DatabaseManagement\Entity\Field\Relational;

class OneToManyRelation extends RelationalField
{
    public function __construct(
        string $internalName,
        string $relatedToDefinition,
        ?string $relatedByInternalName,
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
}