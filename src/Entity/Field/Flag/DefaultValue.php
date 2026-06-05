<?php

namespace NewDavis\DatabaseManagement\Entity\Field\Flag;

use NewDavis\DatabaseManagement\Entity\EntityDefinitionInterface;
use NewDavis\DatabaseManagement\Entity\Field\Field;

class DefaultValue implements Flag
{
    public function __construct(
        private readonly mixed $defaultValue
    ) {
    }

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
        return sprintf(<<<SQL
DEFAULT %s
SQL, $this->defaultValue);
    }
}