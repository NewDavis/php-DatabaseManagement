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
        $relatedDefinition = $this->table->getRegistry()->getDefinitionByDefinitionClass($field->getRelatedToDefinition());
        $relatedField = $this->table->getDefinedFields()->getRelatedField($field, $relatedDefinition);

        $relatedEntityName = $this->table->getRegistry()->getDefinitionByDefinitionClass(
            $field->getRelatedToDefinition()
        )->getEntityName();

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

        foreach ($field->getFlags()->filterByType(FlagType::INLINE_CONSTRAINT) as $flag) {
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

        foreach ($this->table->getDefinedFields()->filter(FkField::class) as $field) {
            $constraintName = $this->buildConstraintName($field);

            if ($constraintName == null) continue;

            $relatedDefinition = $this->table->getRegistry()->getDefinitionByDefinitionClass(
                $this->table->getDefinedFields()->getRelatedDefinition($field)
            );
            $relatedEntityName = $relatedDefinition->getEntityName();
            $relatedField = $this->table->getDefinedFields()->getRelatedField($field, $relatedDefinition);
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
