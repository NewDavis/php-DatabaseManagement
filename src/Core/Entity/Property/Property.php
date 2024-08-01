<?php

namespace NewDavis\DatabaseManagement\Core\Entity\Property;

class Property
{

    private string $property;
    private string $type;
    private int $length;
    private array $flags;

    public function __construct(string $property, string $type, int $length = 255, array $flags = [])
    {
        $this->property = $property;
        $this->type = $type;
        $this->length = $length;
        $this->flags = $flags;
    }

    /**
     * @return string
     */
    public function getProperty(): string
    {
        return $this->property;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return int
     */
    public function getLength(): int
    {
        return $this->length;
    }

    /**
     * @return array
     */
    public function getFlags(): array
    {
        return $this->flags;
    }

}