<?php

namespace NewDavis\DatabaseManagement\Entity\Field\Relational;

use NewDavis\DatabaseManagement\Entity\FieldSerializer\AbstractFieldSerializer;
use NewDavis\DatabaseManagement\Entity\FieldSerializer\DefaultSerializer;

class OneToManyRelation extends RelationalField
{
    public function __construct(
        string $internalName,
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

    public function getSerializer(): AbstractFieldSerializer
    {
        return new DefaultSerializer($this);
    }
}
