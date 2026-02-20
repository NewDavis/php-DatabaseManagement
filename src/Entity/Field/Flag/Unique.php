<?php

namespace NewDavis\DatabaseManagement\Entity\Field\Flag;

use NewDavis\DatabaseManagement\Entity\Field\Field;

class Unique implements Flag
{
    public function getType(): FlagType
    {
        return FlagType::NEW_LINE;
    }

    public function getPriority(): int|null
    {
        return 10;
    }

    public function convert(Field $field, mixed ...$values): string
    {
        return <<<SQL
UNIQUE KEY `uniq_{$field->getInternalName()}` ({$field->getInternalName()})
SQL;
    }
}