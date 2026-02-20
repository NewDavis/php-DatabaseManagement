<?php

namespace NewDavis\DatabaseManagement\Entity\Field\Flag;

enum ConstraintActions
{
    case CASCADE;
    case SET_NULL;
    case NO_ACTION;
}
