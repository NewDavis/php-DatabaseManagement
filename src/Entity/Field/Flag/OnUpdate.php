<?php

namespace NewDavis\DatabaseManagement\Entity\Field\Flag;

use NewDavis\DatabaseManagement\Entity\Field\Field;

class OnUpdate implements Flag, ForeignKeyFlag
{
    public function __construct(
        private readonly ConstraintActions $action
    ) {
    }

    public function getType(): FlagType
    {
        return FlagType::INLINE_CONSTRAINT;
    }

    public function getPriority(): int|null
    {
        return null;
    }

    public function convert(Field $field, ...$values): string
    {
        return sprintf(<<<SQL
ON UPDATE %s
SQL, $this->action->name);
    }
}