<?php

namespace DatabaseManagement\Core\Entity\Property\Flags;

class Unique implements Flag
{
    public function inlineFlag(): bool
    {
        return false;
    }

}