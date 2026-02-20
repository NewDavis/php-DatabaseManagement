<?php

namespace NewDavis\DatabaseManagement\Entity\Field\Relational;

interface RelationalFieldInterface
{
    public function getRelatedToDefinition(): string;
    public function getRelatedByInternalName(): string;
    public function getRelatedToInternalName(): string;
    public function shouldAutoload(): bool;
}