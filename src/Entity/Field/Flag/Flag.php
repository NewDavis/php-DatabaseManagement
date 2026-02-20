<?php

namespace NewDavis\DatabaseManagement\Entity\Field\Flag;

use NewDavis\DatabaseManagement\Entity\Field\Field;

interface Flag
{
    public function getType(): FlagType;
    public function getPriority(): int|null;
    public function convert(Field $field, mixed ...$values): string;
}