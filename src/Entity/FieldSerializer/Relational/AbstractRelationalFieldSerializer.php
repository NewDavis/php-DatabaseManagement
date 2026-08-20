<?php

namespace NewDavis\DatabaseManagement\Entity\FieldSerializer\Relational;

use NewDavis\DatabaseManagement\Entity\Field\Relational\RelationalField;
use NewDavis\DatabaseManagement\Entity\FieldSerializer\AbstractFieldSerializer;
use NewDavis\DatabaseManagement\Entity\FieldSerializer\FieldSerializerInterface;

abstract class AbstractRelationalFieldSerializer extends AbstractFieldSerializer implements FieldSerializerInterface
{
    public function __construct(
        private readonly RelationalField $field
    ) {
        parent::__construct($field);
    }

    /**
     * @return RelationalField
     */
    public function getField(): RelationalField
    {
        return $this->field;
    }
}
