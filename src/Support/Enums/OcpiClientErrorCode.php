<?php

namespace Ocpi\Support\Enums;

enum OcpiClientErrorCode: int
{
    case Generic = 2000;
    case InvalidParameters = 2001;
    case NotEnoughInformation = 2002;
    case UnknownLocation = 2003;
    case UnknownToken = 2004;
}
