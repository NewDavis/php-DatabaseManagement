<?php

namespace NewDavis\DatabaseManagement\Entity\FieldSerializer;

use NewDavis\DatabaseManagement\Entity\Field\Field;

abstract class AbstractFieldSerializer implements FieldSerializerInterface
{
    public function __construct(
        private readonly Field $field
    ) {
    }

    /**
     * @return Field
     */
    public function getField(): Field
    {
        return $this->field;
    }
}
