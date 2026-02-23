<?php

namespace NewDavis\DatabaseManagement\Entity\Field\Flag;

use NewDavis\DatabaseManagement\Entity\Field\Field;

interface Flag
{
    /** @return FlagTypeCollection */
    public function getTypes(): FlagTypeCollection;
    public function getPriority(): int|null;
    public function convert(Field $field, FlagType $convertType, ?string $definitionClass = null, array $values = []): string;
}