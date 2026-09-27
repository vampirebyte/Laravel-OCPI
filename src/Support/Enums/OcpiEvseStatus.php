<?php

namespace Ocpi\Support\Enums;

enum OcpiEvseStatus: string
{
    case AVAILABLE = 'AVAILABLE';
    case BLOCKED = 'BLOCKED';
    case CHARGING = 'CHARGING';
    case INOPERATIVE = 'INOPERATIVE';
    case OUTOFORDER = 'OUTOFORDER';
    case PLANNED = 'PLANNED';
    case REMOVED = 'REMOVED';
    case RESERVED = 'RESERVED';
    case UNKNOWN = 'UNKNOWN';
}
