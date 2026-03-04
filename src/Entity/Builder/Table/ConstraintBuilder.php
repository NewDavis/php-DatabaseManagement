<?php

namespace NewDavis\DatabaseManagement\Entity\Builder\Table;

use NewDavis\DatabaseManagement\Entity\Field\Field;
use NewDavis\DatabaseManagement\Entity\Field\Flag\Flag;
use NewDavis\DatabaseManagement\Entity\Field\Flag\FlagType;
use NewDavis\DatabaseManagement\Entity\Field\Relational\FkField;

class ConstraintBuilder
{
    public function __construct(
        private readonly TableBuilder $table,
    ) {
    }

    private function buildConstraintName(FkField $field): ?string
    {
        $hashContent = $this->table->getTableName() . '.' . $field->getStorageName();
        $relatedField = $this->table->getDefinedFields()->getRelatedField($field);

        $relatedEntityName = $field->getRelatedToDefinition()::getEntityName();

        $hashContent .= '|' .
            $relatedEntityName .
            '.' .
            $relatedField->getStorageName();

        $indexHash = hash('sha256', $hashContent, true);
        $shorten = substr($indexHash, 0, 16);
        $shortHex = bin2hex($shorten);

        return 'fk_' . $shortHex;
    }

    private function buildConstraintFlags(FkField $field): string
    {
        $flags = [];

        foreach (
            array_filter(
                $field->getFlags(),
                fn (Flag $flag) => $flag->getTypes()->hasType(FlagType::INLINE_CONSTRAINT)
            ) as $flag
        ) {
            switch (get_class($flag)) {
                default:
                    $flags[] = $flag->convert(
                        $field,
                        FlagType::INLINE_CONSTRAINT,
                        $this->table->getDefinition()
                    );
                    break;
            }
        }

        return implode(",\n", $flags);
    }

    public function build(): string
    {
        $constraints = [];

        foreach (
            array_filter(
                $this->table->getDefinedFields()->getFields(),
                fn (Field $field) => $field instanceof FkField
            )
            as $field
        ) {
            $constraintName = $this->buildConstraintName($field);

            if ($constraintName == null) continue;

            $relatedEntityName = $this->table->getDefinedFields()->getRelatedDefinition($field)::getEntityName();
            $relatedField = $this->table->getDefinedFields()->getRelatedField($field);
            $flags = $this->buildConstraintFlags($field);

            $constraint = <<<SQL
CONSTRAINT `{$constraintName}`
    FOREIGN KEY (`{$field->getStorageName()}`)
    REFERENCES `{$relatedEntityName}` (`{$relatedField->getStorageName()}`)
    {$flags}
SQL;

            $constraints[] = $constraint;
        }

        return implode(",\n", $constraints);
    }

}