<?php

namespace NewDavis\DatabaseManagement\Entity\Field\Relational;

interface RelatedToInterface
{
    public function getRelatedToDefinition(): string;
}