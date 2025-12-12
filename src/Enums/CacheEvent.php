<?php

namespace Mostafaznv\LaraCache\Enums;


enum CacheEvent
{
    case RETRIEVED;
    case CREATED;
    case UPDATED;
    case DELETED;
    case RESTORED;
}
