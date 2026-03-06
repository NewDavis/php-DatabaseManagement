<?php

namespace NewDavis\DatabaseManagement\Entity\Field\Flag;

use NewDavis\DatabaseManagement\Entity\EntityDefinitionInterface;
use NewDavis\DatabaseManagement\Entity\Field\Field;

class Unique implements Flag
{
    public function __construct(
        private readonly UniqueConvertion $convertion = UniqueConvertion::DEFAULT,
        private readonly array $storageNames = []
    ) {
    }

    public function getTypes(): FlagTypeCollection
    {
        return new FlagTypeCollection([
            FlagType::NEW_LINE
        ]);
    }

    public function getPriority(): int|null
    {
        return 10;
    }

    public function convert(Field $field, FlagType $convertType, ?EntityDefinitionInterface $definition = null, array $values = []): string
    {
        switch ($this->convertion) {
            case UniqueConvertion::MULTIPLE:
                $keys = implode(', ', array_values($this->storageNames));

                return <<<SQL
UNIQUE ({$keys})
SQL;
            default:
                return <<<SQL
UNIQUE KEY `uniq_{$field->getStorageName()}` ({$field->getStorageName()})
SQL;
        }
    }
}