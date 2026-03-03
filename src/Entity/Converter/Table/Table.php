<?php

namespace NewDavis\DatabaseManagement\Entity\Converter\Table;

use NewDavis\DatabaseManagement\Entity\Field\Field;
use NewDavis\DatabaseManagement\Entity\Field\Flag\Flag;
use NewDavis\DatabaseManagement\Entity\Field\Flag\FlagType;
use NewDavis\DatabaseManagement\Entity\Field\Flag\Index;
use NewDavis\DatabaseManagement\Entity\Field\Flag\PrimaryKey;
use NewDavis\DatabaseManagement\Entity\Field\Relational\FkField;
use NewDavis\DatabaseManagement\Entity\Field\Relational\ManyToManyRelation;
use NewDavis\DatabaseManagement\Entity\Field\Relational\ManyToOneRelation;
use NewDavis\DatabaseManagement\Entity\Field\Relational\OneToOneRelation;
use NewDavis\DatabaseManagement\Entity\Field\Relational\RelationalField;
use NewDavis\DatabaseManagement\Entity\Field\Scalar\ScalarField;
use NewDavis\DatabaseManagement\Entity\Field\StorableInterface;
use NewDavis\DatabaseManagement\Entity\Field\SupportsFlags;
use NewDavis\DatabaseManagement\Entity\FieldCollection;

class Table
{
    /** @var array<Table> */
    private array $mappingTables = [];

    /**
     * @param string $tableName
     * @param FieldCollection $definedFields
     */
    public function __construct(
        private readonly string $tableName,
        private readonly FieldCollection $definedFields,
        private readonly ?string $definition = null
    ) {
        $this->createMappingTables();
    }

    private function getPrimaryKeys(): array
    {
        $primaryKeys = [];

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

            $primaryKeys[] = '`' . $field->getStorageName() . '`';
        }

        return $primaryKeys;
    }

    private function buildConstraintName(RelationalField $field): ?string
    {
        switch (get_class($field)) {
            case ManyToOneRelation::class:
            case OneToOneRelation::class:
                $hashContent = $this->tableName . '.' . $field->getStorageName();
                break;
            case ManyToManyRelation::class:
                // TODO
                return null;
            default:
                return null;
        }

        $relatedField = $this->definedFields->getRelatedField($field);

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

    private function buildConstraints(): string
    {
        $constraints = [];

        foreach (
            array_filter(
                $this->definedFields->getFields(),
                fn (Field $field) => $field instanceof RelationalField
            )
            as $field
        ) {
            $constraintName = $this->buildConstraintName($field);

            if ($constraintName == null) continue;

            $fkField = $this->definedFields->getForeignKeyFieldByRelationalField($field);

            $relatedEntityName = $this->definedFields->getRelatedDefinition($field)::getEntityName();
            $relatedField = $this->definedFields->getRelatedField($field);

            $constraint = <<<SQL
CONSTRAINT `{$constraintName}`
    FOREIGN KEY (`{$fkField->getStorageName()}`)
    REFERENCES `{$relatedEntityName}` (`{$relatedField->getStorageName()}`)
SQL;

            // TODO Constraint Flags

            $constraints[] = $constraint;
        }

        return implode(",\n", $constraints);
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

    private function buildIndexName(SupportsFlags $field): string
    {
        $hashContent = $this->tableName . '.' . $field->getStorageName();

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
                            $this->getPrimaryKeys()
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

    private function buildMappingTableName(ManyToManyRelation $relation): string
    {
        $currentTableName = $this->tableName;
        $currentInternalName = $relation->getInternalName();

        $relateTableName = $relation->getRelatedToDefinition()::getEntityName();
        $relatedInternalName = $relation->getRelatedToInternalName();

        $unsortedTableNames = [
            $currentTableName,
            $relateTableName
        ];
        $sortedTableNames = sort($unsortedTableNames);

        return "{$this->tableName}_{$relation->getInternalName()}_";
    }

    private function createMappingTables(): void
    {
        foreach (
            array_filter(
                $this->definedFields->getFields(),
                fn (Field $field) => $field instanceof ManyToManyRelation
            )
            as $manyToManyField
        ) {


            $mappingEntityName = '';
            $mappingFields = new FieldCollection([
                new FkField(),
                new FkField()
            ], $mappingEntityName);
        }
    }

    public function build(): string
    {
        $properties = $this->buildProperties();
        $newLineFlags = $this->buildNewLineFlags();
        $constraints = $this->buildConstraints();

        return <<<SQL
CREATE TABLE IF NOT EXISTS `{$this->tableName}` (
{$properties},
{$newLineFlags},
{$constraints}
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
