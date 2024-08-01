<?php

namespace DatabaseManagement\Core\Entity\Property\Flags;

class Required implements Flag
{

    public function inlineFlag(): bool
    {
        return true;
    }

}