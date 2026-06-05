<?php

namespace NewDavis\DatabaseManagement\Entity\Builder\Write;

enum WriteAction
{
    case CREATE;
    case UPDATE;
    case UPSERT;
}