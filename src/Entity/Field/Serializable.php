<?php

namespace NewDavis\DatabaseManagement\Entity\Field;

use NewDavis\DatabaseManagement\Entity\FieldSerializer\AbstractFieldSerializer;

interface Serializable
{
    public function getSerializer(): ?AbstractFieldSerializer;
}