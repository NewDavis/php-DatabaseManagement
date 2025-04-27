<?php

namespace NewDavis\DatabaseManagement\Core\Entity\Field\Relation;

class OneToManyRelation extends RelationField
{

    public function __construct(
        string $relationInternalName,
        string $relatedToDefinition,
        string $relatedByInternalName = 'id',
        string $relatedToInternalName = 'id',
        bool $autoload = false
    ) {
        parent::__construct(
            $relationInternalName,
            null,
            $relatedToDefinition,
            $relatedByInternalName,
            $relatedToInternalName,
            $autoload
        );
    }

}