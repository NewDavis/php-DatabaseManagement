<?php

namespace NewDavis\DatabaseManagement\Entity\Builder\Table;

use NewDavis\DatabaseManagement\Entity\Field\Field;
use NewDavis\DatabaseManagement\Entity\Field\Flag\Flag;
use NewDavis\DatabaseManagement\Entity\Field\Flag\FlagType;
use NewDavis\DatabaseManagement\Entity\Field\Flag\Nullable;
use NewDavis\DatabaseManagement\Entity\Field\Scalar\IdField;
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

    private function buildPropertyFlags(ScalarField $field, bool $temp = false): string
    {
        $flags = [];

        if ($temp && !($field instanceof IdField)) {
            $flag = new Nullable();

            return $flag->convert(
                $field,
                FlagType::INLINE_PROPERTY,
                $this->table->getDefinition()
            );
        }

        foreach ($field->getFlags()->filterByType(FlagType::INLINE_PROPERTY) as $flag) {
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

    public function build(bool $temp = false): string
    {
        $properties = [];

        foreach ($this->table->getDefinedFields()->filter(ScalarField::class) as $field) {
            $properties[] = <<<SQL
`{$field->getStorageName()}` {$this->buildPropertyType($field)} {$this->buildPropertyFlags($field, $temp)}
SQL;
        }

        return implode(",\n", $properties);
    }
}