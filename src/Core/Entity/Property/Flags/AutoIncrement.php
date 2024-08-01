<?php

namespace DatabaseManagement\Core\Entity\Property\Flags;

class AutoIncrement implements Flag
{
    public function inlineFlag(): bool
    {
        return true;
    }

}