<?php

namespace NewDavis\DatabaseManagement\Entity\Field\Relational;

use NewDavis\DatabaseManagement\Entity\Field\Flag\Unique;
use NewDavis\DatabaseManagement\Entity\Field\Flag\UniqueConvertion;
use NewDavis\DatabaseManagement\Entity\Field\StorableInterface;
use NewDavis\DatabaseManagement\Entity\FieldSerializer\AbstractFieldSerializer;
use NewDavis\DatabaseManagement\Entity\FieldSerializer\DefaultSerializer;

class OneToOneRelation extends RelationalField implements StorableInterface, HasForeignKeyInterface
{
    private ?FkField $fkField = null;

    public function __construct(
        string $internalName,
        private readonly string $storageName,
        string $relatedToDefinition,
        string $relatedToInternalName,
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

        if (count($this->fkField->getFlags()->filter(Unique::class)) !== 0) return;

        $this->fkField->getFlags()->add(new Unique(UniqueConvertion::DEFAULT));
    }
}
