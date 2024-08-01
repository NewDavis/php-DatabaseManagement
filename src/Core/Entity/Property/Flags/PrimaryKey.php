<?php

namespace DatabaseManagement\Core\Entity\Property\Flags;

class PrimaryKey implements Flag
{
    public function inlineFlag(): bool
    {
        return false;
    }
}