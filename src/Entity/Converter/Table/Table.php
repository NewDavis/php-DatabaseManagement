<?php

namespace NewDavis\DatabaseManagement\Entity\Converter\Table;

use Doctrine\DBAL\Schema\Exception\UniqueConstraintDoesNotExist;
use NewDavis\DatabaseManagement\Entity\Field\Field;
use NewDavis\DatabaseManagement\Entity\Field\Flag\AutoIncrement;
use NewDavis\DatabaseManagement\Entity\Field\Flag\DefaultValue;
use NewDavis\DatabaseManagement\Entity\Field\Flag\Flag;
use NewDavis\DatabaseManagement\Entity\Field\Flag\FlagType;
use NewDavis\DatabaseManagement\Entity\Field\Flag\Index;
use NewDavis\DatabaseManagement\Entity\Field\Flag\Nullable;
use NewDavis\DatabaseManagement\Entity\Field\Flag\OnUpdate;
use NewDavis\DatabaseManagement\Entity\Field\Flag\PrimaryKey;
use NewDavis\DatabaseManagement\Entity\Field\Flag\Required;
use NewDavis\DatabaseManagement\Entity\Field\Flag\Unique;
use NewDavis\DatabaseManagement\Entity\Field\Relational\RelationalField;
use NewDavis\DatabaseManagement\Entity\Field\Scalar\ScalarField;
use NewDavis\DatabaseManagement\Entity\Field\StorableInterface;
use NewDavis\DatabaseManagement\Entity\Field\SupportsFlags;
use NewDavis\DatabaseManagement\Entity\FieldCollection;

class Table
{
    /** @var array<Table> */
    private array $mappingTables = [];

    /** @var array<string> */
    private array $primaryKeys = [];

    /** @var array<string> */
    private array $foreignKeys = [];

    /**
     * @param string $tableName
     * @param FieldCollection $definedFields
     */
    public function __construct(
        private readonly string $tableName,
        private readonly FieldCollection $definedFields,
        private readonly ?string $definition = null
    ) {
        $this->collectPrimaryKeys();
    }

    private function collectPrimaryKeys(): Table
    {
        foreach ($this->definedFields->getFields() as $field) {
            if (
                !$field instanceof SupportsFlags ||
                !$field instanceof StorableInterface
            ) continue;

            $isPrimaryKey = array_filter(
                $field->getFlags(),
                fn (Flag $flag) => $flag instanceof PrimaryKey
            );

            if (!$isPrimaryKey) continue;

            $this->primaryKeys[] = '`' . $field->getStorageName() . '`';
        }

        return $this;
    }

    private function buildForeignKeys(): string
    {
        return <<<SQL

SQL;
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
                    $flags[] = $flag->convert($field, FlagType::INLINE_PROPERTY, $this->definition);
                    break;
            }
        }

        return implode(" ", $flags);
    }

    private function buildProperties(): string
    {
        $properties = [];

        foreach (
            array_filter(
                $this->definedFields->getFields(),
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

    private function buildIndexName(Field $field): string
    {
        $hashContent = $this->tableName . '.' . $field->getStorageName();

        if ($field instanceof RelationalField) {
            $relatedField = $field->getRelatedToDefinition()::getFields()->getByInternalName(
                $field->getRelatedToInternalName()
            );

            $hashContent .= '|' .
                $field->getRelatedToDefinition()::getEntityName() .
                '.' .
                $relatedField->getStorageName();
        }

        $indexHash = hash('sha256', $hashContent, true);
        $shorten = substr($indexHash, 0, 16);
        $shortHex = bin2hex($shorten);

        return 'idx_' . $shortHex;
    }

    private function buildNewLineFlags(): string
    {
        $flags = [];

        foreach ($this->definedFields->getFields() as $field) {
            if (!($field instanceof SupportsFlags)) continue;

            foreach (
                array_filter(
                    $field->getFlags(),
                    fn (Flag $flag) => $flag->getTypes()->hasType(FlagType::NEW_LINE)
                ) as $flag
            ) {
                switch (get_class($flag)) {
                    case Index::class:
                        $flags[] = $flag->convert($field, FlagType::NEW_LINE, $this->definition, [
                            $this->buildIndexName($field),
                            $field->getStorageName()
                        ]);
                        break;
                    case PrimaryKey::class:
                        $flags[get_class($flag)] = $flag->convert(
                            $field,
                            FlagType::NEW_LINE,
                            $this->definition,
                            $this->primaryKeys
                        );
                        break;
                    default:
                        $flags[] = $flag->convert($field, FlagType::NEW_LINE, $this->definition);
                        break;
                }
            }
        }

        return implode(",\n", $flags);
    }

    private function createMappingTables(): Table
    {
        return $this;
    }

    public function build(): string
    {
        $properties = $this->buildProperties();
        $newLineFlags = $this->buildNewLineFlags();

        return <<<SQL
CREATE TABLE IF NOT EXISTS `{$this->tableName}` (
{$properties},
{$newLineFlags}
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
    }

    public static function fromDefinition(string $definitionClass): Table
    {
        return new Table(
            $definitionClass::getEntityName(),
            $definitionClass::getFields(),
            $definitionClass
        );
    }
}