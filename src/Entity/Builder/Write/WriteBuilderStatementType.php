<?php

namespace NewDavis\DatabaseManagement\Entity\Builder\Write;

enum WriteBuilderStatementType
{
    case RELATED;
    case MAIN;
    case MAPPING;
}