<?php

namespace NewDavis\DatabaseManagement\Entity\Field\Flag;

use NewDavis\DatabaseManagement\Entity\Field\Field;

class CaseSensitive implements Flag
{
    public function getTypes(): FlagTypeCollection
    {
        return new FlagTypeCollection([
            FlagType::INLINE_PROPERTY
        ]);
    }

    public function getPriority(): int|null
    {
        return null;
    }

    public function convert(Field $field, FlagType $convertType, ?string $definitionClass = null, array $values = []): string
    {
        return <<<SQL
CHARACTER SET utf8mb4 COLLATE utf8mb4_bin
SQL;
    }
}