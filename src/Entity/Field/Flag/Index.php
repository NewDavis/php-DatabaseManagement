<?php

namespace NewDavis\DatabaseManagement\Entity\Field\Flag;

use NewDavis\DatabaseManagement\Entity\Field\Field;

class Index implements Flag
{
    public function getTypes(): FlagTypeCollection
    {
        return new FlagTypeCollection([
            FlagType::NEW_LINE
        ]);
    }

    public function getPriority(): int|null
    {
        return null;
    }

    public function convert(Field $field, FlagType $convertType, ?string $definitionClass = null, array $values = []): string
    {
        return sprintf(<<<SQL
INDEX %s (`%s`)
SQL, ...$values);
    }
}