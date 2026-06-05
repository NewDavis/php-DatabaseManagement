<?php

namespace NewDavis\DatabaseManagement\Entity\Field\Relational;

interface HasRelationInterface
{
    public function getRelation(): ManyToOneRelation|OneToOneRelation|null;
    public function setRelation(ManyToOneRelation|OneToOneRelation $relation): void;
}