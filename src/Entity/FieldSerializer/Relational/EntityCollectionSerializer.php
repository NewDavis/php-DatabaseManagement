<?php

namespace NewDavis\DatabaseManagement\Entity\FieldSerializer\Relational;

use NewDavis\DatabaseManagement\Entity\EntityCollectionInterface;
use NewDavis\DatabaseManagement\Util\Helper\EntityHelper;

class EntityCollectionSerializer extends AbstractRelationalFieldSerializer
{
    public function encode(mixed $value): EntityCollectionInterface
    {
        if ($value instanceof EntityCollectionInterface) return $value;

        return EntityHelper::createCollectionByCollectionClass(
            EntityHelper::findSuitableCollectionClassByDefinitionClass(
                $this->getField()->getRelatedToDefinition()
            )
        );
    }

    public function decode(mixed $data): EntityCollectionInterface
    {
        return $this->encode($data);
    }

    public function validate(mixed $value): bool
    {
        return $value instanceof EntityCollectionInterface;
    }
}