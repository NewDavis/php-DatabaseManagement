<?php

namespace DatabaseManagement\Core\Entity\Property\Flags;

class Nullable implements Flag
{
    public function inlineFlag(): bool
    {
        return true;
    }
}