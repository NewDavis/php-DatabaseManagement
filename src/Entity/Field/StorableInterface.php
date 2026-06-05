<?php

namespace NewDavis\DatabaseManagement\Entity\Field;

interface StorableInterface
{
    public function getStorageName(): string;
}