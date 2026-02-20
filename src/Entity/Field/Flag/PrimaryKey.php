<?php

namespace NewDavis\DatabaseManagement\Entity\Field\Flag;

use NewDavis\DatabaseManagement\Entity\Field\Field;

class PrimaryKey implements Flag
{
    public function getType(): FlagType
    {
        return FlagType::NEW_LINE;
    }

    public function getPriority(): int|null
    {
        return 0;
    }

    public function convert(Field $field, mixed ...$values): string
    {
        return sprintf(<<<SQL
PRIMARY KEY (%s)
SQL, $values);
    }
}