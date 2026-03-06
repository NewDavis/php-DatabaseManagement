<?php

namespace NewDavis\DatabaseManagement\Entity\Exception\Table;

use NewDavis\DatabaseManagement\Entity\Field\Relational\RelationalFieldInterface;

class RelatedFieldNotFoundException extends \Exception
{
    public function __construct(string $tableName, RelationalFieldInterface $field)
    {
        $relatedTableName = $field->getRelatedToDefinition()::getEntityName();

        parent::__construct(
            "There is no related field in {$relatedTableName} for internalName {$tableName}.{$field->getInternalName()}"
        );
    }
}