<?php

namespace NewDavis\DatabaseManagement\Core\Entity\Field;

class FkField extends Field
{
    public function __construct(
        string $internalName,
        string $storageName,
        private readonly string $relatedDefinitionClass,
        ...$flags
    ) {
        parent::__construct($internalName, 'UUID', -1, $storageName, ...$flags);
    }

    /**
     * @return string
     */
    public function getRelatedDefinitionClass(): string
    {
        return $this->relatedDefinitionClass;
    }

}