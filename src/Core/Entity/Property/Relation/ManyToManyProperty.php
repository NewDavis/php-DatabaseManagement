<?php

namespace DatabaseManagement\Core\Entity\Property\Relation;

use DatabaseManagement\Core\Entity\Property\Flags\PrimaryKey;
use DatabaseManagement\Core\Entity\Property\Flags\Unique;

class ManyToManyProperty extends RelationProperty
{

    private bool $main;

    public function __construct(string $property, string $referencedEntity, bool $main, bool $autoLoad = true, array $flags = [new Unique(), new PrimaryKey()])
    {
        $this->main = $main;

        parent::__construct($property, $referencedEntity, 36, $autoLoad, $flags);
    }

    /**
     * @return bool
     */
    public function isMain(): bool
    {
        return $this->main;
    }

}