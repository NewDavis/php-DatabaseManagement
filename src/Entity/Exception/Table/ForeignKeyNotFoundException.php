<?php

namespace NewDavis\DatabaseManagement\Entity\Exception\Table;

use NewDavis\DatabaseManagement\Entity\Field\Relational\ManyToManyRelation;
use NewDavis\DatabaseManagement\Entity\Field\Relational\ManyToOneRelation;
use NewDavis\DatabaseManagement\Entity\Field\Relational\OneToOneRelation;
use NewDavis\DatabaseManagement\Entity\Field\StorableInterface;

class ForeignKeyNotFoundException extends \Exception
{
    public function __construct(string $tableName, StorableInterface $field)
    {
        switch (get_class($field)) {
            case ManyToOneRelation::class:
            case OneToOneRelation::class:
                parent::__construct(
                    "There is no foreign key in {$tableName} for storageName {$field->getStorageName()}"
                );
                break;
            case ManyToManyRelation::class:
                parent::__construct(
                    "There is no foreign key in {$tableName} for storageName {$field->getInternalName()}"
                );
                break;
        }
    }
}