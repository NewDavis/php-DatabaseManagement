<?php

namespace NewDavis\DatabaseManagement\Entity\Field\Flag;

use NewDavis\DatabaseManagement\Entity\EntityDefinitionInterface;
use NewDavis\DatabaseManagement\Entity\Field\Field;

class OnDelete implements Flag, ForeignKeyFlag
{
    public function __construct(
        private readonly ConstraintActions $action,
        private readonly mixed $customValue = null,
    ) {
    }

    public function getTypes(): FlagTypeCollection
    {
        return new FlagTypeCollection([
            FlagType::INLINE_CONSTRAINT
        ]);
    }

    public function getPriority(): int|null
    {
        return null;
    }

    public function convert(Field $field, FlagType $convertType, ?EntityDefinitionInterface $definition = null, array $values = []): string
    {
        return sprintf(<<<SQL
ON DELETE %s
SQL, ($this->action == ConstraintActions::CUSTOM ? $this->customValue : $this->action->name));
    }
}