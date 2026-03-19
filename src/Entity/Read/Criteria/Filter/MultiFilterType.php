<?php

namespace NewDavis\DatabaseManagement\Entity\Read\Criteria\Filter;

enum MultiFilterType: string
{
    case AND = 'AND';
    case OR = 'OR';
}
