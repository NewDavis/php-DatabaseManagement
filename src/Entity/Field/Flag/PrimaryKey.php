<?php

namespace NewDavis\DatabaseManagement\Entity\Field\Flag;

use NewDavis\DatabaseManagement\Entity\EntityDefinitionInterface;
use NewDavis\DatabaseManagement\Entity\Field\Field;

class PrimaryKey implements Flag
{
    public function getTypes(): FlagTypeCollection
    {
        return new FlagTypeCollection([
            FlagType::NEW_LINE
        ]);
    }

    public function getPriority(): int|null
    {
        return 0;
    }

    public function convert(Field $field, FlagType $convertType, ?EntityDefinitionInterface $definition = null, array $values = []): string
    {
        return sprintf(<<<SQL
PRIMARY KEY (%s)
SQL, implode(', ', $values));
    }
}