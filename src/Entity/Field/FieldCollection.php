<?php

namespace NewDavis\DatabaseManagement\Entity\Field;

use NewDavis\DatabaseManagement\Entity\EntityDefinitionInterface;
use NewDavis\DatabaseManagement\Entity\Exception\Table\FieldNotFoundException;
use NewDavis\DatabaseManagement\Entity\Exception\Table\ForeignKeyNotFoundException;
use NewDavis\DatabaseManagement\Entity\Exception\Table\RelatedFieldNotFoundException;
use NewDavis\DatabaseManagement\Entity\Field\Relational\FkField;
use NewDavis\DatabaseManagement\Entity\Field\Relational\RelationalFieldInterface;

class FieldCollection
{
    public function __construct(
        private readonly array $fields,
        private readonly ?string $entityName,
    ) {
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
}
