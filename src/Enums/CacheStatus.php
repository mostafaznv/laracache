<?php

namespace Mostafaznv\LaraCache\Enums;


enum CacheStatus
{
    case NOT_CREATED;
    case CREATING;
    case CREATED;
    case DELETED;
}
