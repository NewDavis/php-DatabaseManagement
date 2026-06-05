<?php

namespace NewDavis\DatabaseManagement\Entity\Field\Flag;

enum FlagType
{
    case INVISIBLE;
    case INLINE_PROPERTY;
    case INLINE_CONSTRAINT;
    case NEW_LINE;
}
