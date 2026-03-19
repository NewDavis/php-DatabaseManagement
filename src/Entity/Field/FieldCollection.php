<?php

namespace NewDavis\DatabaseManagement\Entity\Field;

use Exception;
use NewDavis\DatabaseManagement\Entity\EntityDefinitionInterface;
use NewDavis\DatabaseManagement\Entity\Exception\Table\FieldNotFoundException;
use NewDavis\DatabaseManagement\Entity\Exception\Table\ForeignKeyNotFoundException;
use NewDavis\DatabaseManagement\Entity\Exception\Table\RelatedFieldNotFoundException;
use NewDavis\DatabaseManagement\Entity\Field\Relational\FkField;
use NewDavis\DatabaseManagement\Entity\Field\Relational\ManyToOneRelation;
use NewDavis\DatabaseManagement\Entity\Field\Relational\OneToOneRelation;
use NewDavis\DatabaseManagement\Entity\Field\Relational\RelationalFieldInterface;
use Traversable;

class FieldCollection implements \IteratorAggregate, \Countable
{
    public function __construct(
        private array $fields,
        private readonly ?string $entityName,
    ) {
        $this->mapFkFieldWithRelation();
    }

    public function add(Field $field): void
    {
        $this->fields[] = $field;
    }

    public function filter(string $className): array
    {
        return array_values(
            array_filter(
                $this->fields,
                fn(Field $field) => $field instanceof $className,
            )
        );
    }

    public function getByStorageName(string $storageName): Field
    {
        return array_values(array_filter(
            $this->filter(StorableInterface::class),
            function (StorableInterface $storable) use ($storageName) {
                return $storable->getStorageName() === $storageName;
            }
        ))[0] ?? throw new FieldNotFoundException($this->getEntityName(), $storageName);
    }

    public function getByInternalName(string $internalName): Field
    {
        return array_values(array_filter($this->fields, function (Field $field) use ($internalName) {
            return $field->getInternalName() === $internalName;
        }))[0] ?? throw new FieldNotFoundException($this->getEntityName(), $internalName);
    }

    public function getForeignKeyFieldByStorableRelationalField(StorableInterface $relationalField): FkField
    {
        return array_values(array_filter(
            $this->fields,
            fn (Field $field) => $field instanceof FkField &&
                $field->getStorageName() === $relationalField->getStorageName() &&
                $field->getRelatedToDefinition() === $relationalField->getRelatedToDefinition()
        ))[0] ?? throw new ForeignKeyNotFoundException($this->getEntityName(), $relationalField);
    }

    public function getRelatedDefinition(RelationalFieldInterface $field): string
    {
        return $field->getRelatedToDefinition();
    }

    public function getRelatedField(RelationalFieldInterface $field, EntityDefinitionInterface $relatedDefinition): StorableInterface
    {
        return array_values(array_filter(
            $relatedDefinition->getFields()->getFields(),
            fn (Field $relatedDefinitionField) => $field instanceof StorableInterface && $relatedDefinitionField->getInternalName() === $field->getRelatedToInternalName()
        ))[0] ?? throw new RelatedFieldNotFoundException($this->getEntityName(), $field);
    }

    /** @return array<Field> */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * @return string
     */
    public function getEntityName(): string
    {
        return $this->entityName;
    }

    private function mapFkFieldWithRelation()
    {
        $relations = [
            ...$this->filter(ManyToOneRelation::class),
            ...$this->filter(OneToOneRelation::class)
        ];

        /** @var ManyToOneRelation|OneToOneRelation $relation */
        foreach ($relations as $relation) {
            $fkField = $this->getByStorageName($relation->getStorageName());

            if (!$fkField instanceof FkField) {
                throw new ForeignKeyNotFoundException($this->getEntityName(), $relation);
            }

            $fkField->setRelation($relation);
            $relation->setForeignKey($fkField);
        }
    }

    public function getIterator(): Traversable
    {
        return new \ArrayIterator($this->fields);
    }

    public function count(): int
    {
        return count($this->fields);
    }
}
