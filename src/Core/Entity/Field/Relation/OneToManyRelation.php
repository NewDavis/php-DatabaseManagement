<?php

namespace NewDavis\DatabaseManagement\Core\Entity\Field\Relation;

class OneToManyRelation extends RelationField
{

    public function __construct(
        string $internalName,
        string $relatedToDefinition,
        string $relatedBy = 'id',
        string $relatedTo = 'id',
        bool   $autoload = false
    ) {
        parent::__construct(
            $internalName,
            null,
            $relatedToDefinition,
            $relatedBy,
            $relatedTo,
            $autoload
        );
    }

}