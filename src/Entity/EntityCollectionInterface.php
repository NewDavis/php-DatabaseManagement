<?php

namespace NewDavis\DatabaseManagement\Entity;

interface EntityCollectionInterface extends \IteratorAggregate, \Countable
{
    public static function getDefinitionClass(): string;
}