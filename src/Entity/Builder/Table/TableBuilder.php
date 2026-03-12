<?php

namespace NewDavis\DatabaseManagement\Entity\Builder\Table;

use NewDavis\DatabaseManagement\Entity\EntityDefinitionInterface;
use NewDavis\DatabaseManagement\Entity\EntityRegistry;
use NewDavis\DatabaseManagement\Entity\Field\FieldCollection;
use NewDavis\DatabaseManagement\Entity\Field\Flag\Flag;
use NewDavis\DatabaseManagement\Entity\Field\Flag\FlagType;
use NewDavis\DatabaseManagement\Entity\Field\Flag\Index;
use NewDavis\DatabaseManagement\Entity\Field\Flag\PrimaryKey;
use NewDavis\DatabaseManagement\Entity\Field\StorableInterface;
use NewDavis\DatabaseManagement\Entity\Field\SupportsFlags;

class TableBuilder
{
    private readonly MappingTableBuilder $mappingTableBuilder;
    private readonly PropertyBuilder $propertyBuilder;
    private readonly ConstraintBuilder $constraintBuilder;

    /**
     * @param string $tableName
     * @param FieldCollection $definedFields
     * @param null|EntityDefinitionInterface $definition
     */
    public function __construct(
        private readonly string $tableName,
        private readonly FieldCollection $definedFields,
        private readonly EntityRegistry $registry,
        private readonly ?EntityDefinitionInterface $definition = null
    ) {
        $this->mappingTableBuilder = new MappingTableBuilder($this);
        $this->propertyBuilder = new PropertyBuilder($this);
        $this->constraintBuilder = new ConstraintBuilder($this);
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

    public function build(): string
    {
        $properties = $this->propertyBuilder->build();
        $newLineFlags = $this->buildNewLineFlags();
        $constraints = $this->constraintBuilder->build();

        $mappingTables = implode("\n", array_map(
            fn(TableBuilder $mappingTable) => $mappingTable->build(),
            $this->mappingTableBuilder->getMappingTables()
        ));

        $contents = implode(",\n", array_filter(
            [
                $properties,
                $newLineFlags,
                $constraints
            ],
            fn(string $content) => trim($content) !== ''
        ));

        return <<<SQL
CREATE TABLE IF NOT EXISTS `{$this->tableName}` (
{$contents}
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

{$mappingTables}
SQL;
    }

    /**
     * @return string
     */
    public function getTableName(): string
    {
        return $this->tableName;
    }

    /**
     * @return FieldCollection
     */
    public function getDefinedFields(): FieldCollection
    {
        return $this->definedFields;
    }

    /**
     * @return EntityRegistry
     */
    public function getRegistry(): EntityRegistry
    {
        return $this->registry;
    }

    /**
     * @return EntityDefinitionInterface|null
     */
    public function getDefinition(): ?EntityDefinitionInterface
    {
        return $this->definition;
    }

    public static function fromDefinition(EntityRegistry $registry, EntityDefinitionInterface $definition): TableBuilder
    {
        return new TableBuilder(
            $definition->getEntityName(),
            $definition->getFields(),
            $registry,
            $definition
        );
    }
}
