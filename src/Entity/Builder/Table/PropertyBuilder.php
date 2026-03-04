<?php

namespace NewDavis\DatabaseManagement\Entity\Builder\Table;

use NewDavis\DatabaseManagement\Entity\Field\Field;
use NewDavis\DatabaseManagement\Entity\Field\Flag\Flag;
use NewDavis\DatabaseManagement\Entity\Field\Flag\FlagType;
use NewDavis\DatabaseManagement\Entity\Field\Scalar\ScalarField;

class PropertyBuilder
{
    public function __construct(
        private readonly TableBuilder $table,
    ) {
    }

    private function buildPropertyType(ScalarField $field): string
    {
        return $field->getType() . ($field->getLength() != null ? '(' . $field->getLength() . ')' : '');
    }

    private function buildPropertyFlags(ScalarField $field): string
    {
        $flags = [];

        foreach (
            array_filter(
                $field->getFlags(),
                fn (Flag $flag) => $flag->getTypes()->hasType(FlagType::INLINE_PROPERTY)
            ) as $flag
        ) {
            switch (get_class($flag)) {
                default:
                    $flags[] = $flag->convert(
                        $field,
                        FlagType::INLINE_PROPERTY,
                        $this->table->getDefinition()
                    );
                    break;
            }
        }

        return implode(" ", $flags);
    }

    public function build(): string
    {
        $properties = [];

        foreach (
            array_filter(
                $this->table->getDefinedFields()->getFields(),
                fn (Field $field) => $field instanceof ScalarField
            )
            as $field
        ) {
            $properties[] = <<<SQL
`{$field->getStorageName()}` {$this->buildPropertyType($field)} {$this->buildPropertyFlags($field)}
SQL;
        }

        return implode(",\n", $properties);
    }
}