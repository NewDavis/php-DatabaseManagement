<?php

namespace NewDavis\DatabaseManagement\Entity\Field\Relational;

interface RelationalFieldInterface extends AutoloadableInterface
{
    public function getRelatedToDefinition(): string;
    public function getRelatedByInternalName(): string;
    public function getRelatedToInternalName(): string;
}