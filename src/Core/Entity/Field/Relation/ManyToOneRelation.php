<?php

namespace NewDavis\DatabaseManagement\Core\Entity\Field\Relation;

class ManyToOneRelation extends RelationField
{

    public function __construct(
        string $internalName,
        string $storageName,
        string $relatedToDefinition,
        string $relatedTo = 'id',
        bool   $autoload = false
    ) {
        parent::__construct(
            $internalName,
            $storageName,
            $relatedToDefinition,
            null,
            $relatedTo,
            $autoload
        );
    }

}