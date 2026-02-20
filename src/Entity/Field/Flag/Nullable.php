<?php

namespace NewDavis\DatabaseManagement\Entity\Field\Flag;

use NewDavis\DatabaseManagement\Entity\Field\Field;

class Nullable implements Flag
{
    public function getType(): FlagType
    {
        return FlagType::INLINE_PROPERTY;
    }

    public function getPriority(): int|null
    {
        return null;
    }

    public function convert(Field $field, mixed ...$values): string
    {
        return <<<SQL
NULL
SQL;
    }
}