<?php

namespace NewDavis\DatabaseManagement\Core\Entity\Property\Relation;

use NewDavis\DatabaseManagement\Core\Entity\Property\Property;

class RelationProperty extends Property
{

    private string $referencedEntity;
    private bool $autoLoad;

    public function __construct(string $property, string $referencedEntity, int $length = 36, bool $autoLoad = true, array $flags = [])
    {
        parent::__construct($property, 'VARCHAR', $length, $flags);

        $this->referencedEntity = $referencedEntity;
        $this->autoLoad = $autoLoad;
    }

    /**
     * @return string
     */
    public function getReferencedEntity(): string
    {
        return $this->referencedEntity;
    }

    /**
     * @return bool
     */
    public function isAutoLoad(): bool
    {
        return $this->autoLoad;
    }

}