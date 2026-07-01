<?php

namespace Ocpi\Models\Commands\Enums;

enum CommandType: string
{
    case CANCEL_RESERVATION = 'CANCEL_RESERVATION';
    case RESERVE_NOW = 'RESERVE_NOW';
    case START_SESSION = 'START_SESSION';
    case STOP_SESSION = 'STOP_SESSION';
    case UNLOCK_CONNECTOR = 'UNLOCK_CONNECTOR';
}
