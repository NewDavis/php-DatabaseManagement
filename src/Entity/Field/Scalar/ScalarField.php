<?php

namespace NewDavis\DatabaseManagement\Entity\Field\Scalar;

use NewDavis\DatabaseManagement\Entity\Field\Field;
use NewDavis\DatabaseManagement\Entity\Field\Flag\Flag;
use NewDavis\DatabaseManagement\Entity\Field\StorableInterface;
use NewDavis\DatabaseManagement\Entity\Field\SupportsFlags;
use NewDavis\DatabaseManagement\Entity\FieldSerializer\AbstractFieldSerializer;

class ScalarField extends Field implements ScalarFieldInterface, StorableInterface, SupportsFlags
{
    /**
     * @param string $internalName
     * @param string $storageName
     * @param string $type
     * @param int|null $length
     * @param Flag[] $flags
     */
    public function __construct(
        private readonly string $internalName,
        private readonly string $storageName,
        private readonly string $type,
        private readonly int|null $length,
        private readonly array $flags = []
    ) {
        parent::__construct($this->internalName);
    }

    /**
     * @return string
     */
    public function getStorageName(): string
    {
        return $this->storageName;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return int|null
     */
    public function getLength(): ?int
    {
        return $this->length;
    }

    public function getFlags(): array
    {
        return $this->flags;
    }

    public function getSerializer(): ?AbstractFieldSerializer
    {
        return null;
    }
}