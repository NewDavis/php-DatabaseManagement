<?php

namespace NewDavis\DatabaseManagement\Entity\Field\Relational;

use NewDavis\DatabaseManagement\Entity\Field\Flag\Unique;
use NewDavis\DatabaseManagement\Entity\Field\Flag\UniqueConvertion;
use NewDavis\DatabaseManagement\Entity\Field\StorableInterface;
use NewDavis\DatabaseManagement\Entity\FieldSerializer\AbstractFieldSerializer;
use NewDavis\DatabaseManagement\Entity\FieldSerializer\DefaultSerializer;

class ManyToOneRelation extends RelationalField implements StorableInterface, HasForeignKeyInterface
{
    private ?FkField $fkField = null;

    public function __construct(
        string $internalName,
        private readonly string $storageName,
        string $relatedToDefinition,
        string $relatedToInternalName = 'id',
        bool $autoLoad = false
    ) {
        parent::__construct(
            $internalName,
            $relatedToDefinition,
            $relatedToInternalName,
            $autoLoad
        );
    }

    public function getStorageName(): string
    {
        return $this->storageName;
    }

    public function getSerializer(): AbstractFieldSerializer
    {
        return new DefaultSerializer($this);
    }

    public function getForeignKey(): ?FkField
    {
        return $this->fkField;
    }

    public function setForeignKey(FkField $foreignKey): void
    {
        $this->fkField = $foreignKey;
    }
}
