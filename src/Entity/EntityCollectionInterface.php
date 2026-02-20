<?php

namespace NewDavis\DatabaseManagement\Entity;

interface EntityCollectionInterface
{
    public static function getDefinitionClass(): string;
}