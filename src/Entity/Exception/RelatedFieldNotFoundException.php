<?php

namespace NewDavis\DatabaseManagement\Entity\Exception;

use NewDavis\DatabaseManagement\Entity\Field\Relational\RelationalField;

class RelatedFieldNotFoundException extends \Exception
{
    public function __construct(string $tableName, RelationalField $field)
    {
        $relatedTableName = $field->getRelatedToDefinition()::getEntityName();

        parent::__construct(
            "There is no related field in {$relatedTableName} for internalName {$tableName}.{$field->getInternalName()}"
        );
    }
}