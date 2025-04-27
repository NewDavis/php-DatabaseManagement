<?php

namespace NewDavis\DatabaseManagement\Core\Entity\Field\Relation;

class ManyToOneRelation extends RelationField
{

    public function __construct(
        string $relationInternalName,
        string $relationStorageName,
        string $relatedToDefinition,
        string $relatedToInternalName = 'id',
        bool $autoload = false
    ) {
        parent::__construct(
            $relationInternalName,
            $relationStorageName,
            $relatedToDefinition,
            null,
            $relatedToInternalName,
            $autoload
        );
    }

}