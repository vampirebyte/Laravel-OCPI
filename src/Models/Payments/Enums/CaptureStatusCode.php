<?php

namespace Ocpi\Models\Payments\Enums;

/**
 * How the capture of a session's cost on the card went at the PSP.
 */
enum CaptureStatusCode: string
{
    case SUCCESS = 'SUCCESS';
    case PARTIAL_SUCCESS = 'PARTIAL_SUCCESS';
    case FAILED = 'FAILED';
}
