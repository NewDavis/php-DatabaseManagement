<?php

namespace NewDavis\DatabaseManagement\Entity\Field\Relational;

interface AutoloadableInterface
{
    public function shouldAutoLoad(): bool;
}