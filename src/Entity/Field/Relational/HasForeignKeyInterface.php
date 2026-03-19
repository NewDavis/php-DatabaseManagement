<?php

namespace NewDavis\DatabaseManagement\Entity\Field\Relational;

interface HasForeignKeyInterface
{
    public function getForeignKey(): ?FkField;
    public function setForeignKey(FkField $foreignKey): void;
}