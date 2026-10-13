<?php

namespace Ocpi\Models\Payments\Enums;

/**
 * Who makes the invoice for the driver of a session paid on a terminal.
 */
enum InvoiceCreator: string
{
    case CPO = 'CPO';
    case PTP = 'PTP';
}
