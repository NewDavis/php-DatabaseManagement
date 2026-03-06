<?php

namespace NewDavis\DatabaseManagement\Entity\Field\Flag;

use NewDavis\DatabaseManagement\Entity\EntityDefinitionInterface;
use NewDavis\DatabaseManagement\Entity\Field\Field;

class AutoIncrement implements Flag
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

    public function convert(Field $field, FlagType $convertType, ?EntityDefinitionInterface $definition = null, array $values = []): string
    {
        return <<<SQL
AUTO_INCREMENT
SQL;
    }
}