<?php

namespace NewDavis\DatabaseManagement\Core\Entity\Field;

use NewDavis\DatabaseManagement\Core\Entity\Flag\Flag;

class Field
{
    private string $internalName;
    private string $type;
    private int $length;
    private string $storageName;

    /** @var array<Flag> */
    private array $flags;

    public function __construct(string $internalName, string $type, int $length, string $storageName, ...$flags)
    {
        $this->setInternalName($internalName);
        $this->setType($type);
        $this->setLength($length);
        $this->setStorageName($storageName);
        $this->setFlags(...$flags);
    }

    /**
     * @param string $internalName
     */
    public function setInternalName(string $internalName): void
    {
        $this->internalName = $internalName;
    }

    /**
     * @return string
     */
    public function getInternalName(): string
    {
        return $this->internalName;
    }

    /**
     * @param string $storageName
     */
    public function setStorageName(string $storageName): void
    {
        $this->storageName = $storageName;
    }

    /**
     * @return string
     */
    public function getStorageName(): string
    {
        return $this->storageName;
    }

    /**
     * @param string $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param int $length
     */
    public function setLength(int $length): void
    {
        $this->length = $length;
    }

    /**
     * @return int
     */
    public function getLength(): int
    {
        return $this->length;
    }

    public function hasFlag(string $searchedFlag): bool
    {
        foreach ($this->flags as $flag) {
            if ($searchedFlag === get_class($flag)) return true;
        }

        return false;
    }

    /**
     * @param array<Flag> $flags
     */
    public function addFlag(...$flags): void
    {
        $this->flags = $this->filterUniqueClassName(...$flags, ...$this->flags);
    }

    /**
     * @param array<Flag> $flags
     */
    public function setFlags(...$flags): void
    {
        $this->flags = $this->filterUniqueClassName(...$flags);
    }

    /**
     * @param array<Flag> $flags
     * @return array
     */
    private function filterUniqueClassName(...$flags) : array
    {
        $seen = [];
        $result = [];

        foreach ($flags as $flag) {
            $className = get_class($flag);

            if (!isset($seen[$className])) {
                $seen[$className] = true;
                $result[] = $flag;
            }
        }

        return $result;
    }

    /**
     * @return array<Flag>
     */
    public function getFlags(): array
    {
        return $this->flags;
    }

}