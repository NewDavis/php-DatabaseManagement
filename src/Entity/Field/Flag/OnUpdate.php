<?php

namespace NewDavis\DatabaseManagement\Entity\Field\Flag;

use NewDavis\DatabaseManagement\Entity\Field\Field;

class OnUpdate implements Flag, ForeignKeyFlag
{
    public function __construct(
        private readonly ConstraintActions $action,
        private readonly mixed $customValue,
    ) {
    }

    public function getTypes(): FlagTypeCollection
    {
        return new FlagTypeCollection([
            FlagType::INLINE_PROPERTY,
            FlagType::INLINE_CONSTRAINT
        ]);
    }

    public function getPriority(): int|null
    {
        return null;
    }

    public function convert(
        Field $field,
        FlagType $convertType,
        ?string $definitionClass = null,
        array $values = []
    ): string {
        return sprintf(<<<SQL
ON UPDATE %s
SQL, ($this->action == ConstraintActions::CUSTOM ? $this->customValue : $this->action->name));
    }
}