<?php

namespace NewDavis\DatabaseManagement\Core\Entity\Property\Relation;

class OneToManyProperty extends RelationProperty
{

    private string $referencedProperty;

    public function __construct(string $property, string $referencedProperty, string $referencedEntity, bool $autoLoad = true, array $flags = [])
    {
        parent::__construct($property, $referencedEntity, 36, $autoLoad, $flags);

        $this->referencedProperty = $referencedProperty;
    }

    /**
     * @return string
     */
    public function getReferencedProperty(): string
    {
        return $this->referencedProperty;
    }

}