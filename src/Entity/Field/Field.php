<?php

namespace NewDavis\DatabaseManagement\Entity\Field;

use NewDavis\DatabaseManagement\Entity\Field\Flag\Flag;
use NewDavis\DatabaseManagement\Entity\Field\Scalar\ScalarFieldInterface;

class Field implements FieldInterface, ScalarFieldInterface
{
    /** @var Flag[] */
    private readonly array $flags;

    /**
     * @param string $internalName
     * @param string $storageName
     * @param string $type
     * @param int|null $length
     * @param array<Flag> ...$flags
     */
    public function __construct(
        private readonly string $internalName,
        private readonly string $storageName,
        private readonly string $type,
        private readonly int|null $length,
        array ...$flags
    ) {
        $this->flags = $flags;
    }

    /**
     * @return string
     */
    public function getInternalName(): string
    {
        return $this->internalName;
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

    /**
     * @return array<Flag>
     */
    public function getFlags(): array
    {
        return $this->flags;
    }
}