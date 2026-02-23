<?php

namespace NewDavis\DatabaseManagement\Entity;

use NewDavis\DatabaseManagement\Entity\Field\Field;

class FieldCollection
{
    public function __construct(
        private readonly array $fields,
    ) {
    }

    public function getByInternalName(string $internalName): ?Field
    {
        return array_filter($this->fields, function (Field $field) use ($internalName) {
            return $field->getInternalName() === $internalName;
        })[0];
    }

    /** @return array<Field> */
    public function getFields(): array
    {
        return $this->fields;
    }
}