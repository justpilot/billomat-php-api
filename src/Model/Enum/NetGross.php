<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model\Enum;

enum NetGross: string
{
    case NET = 'NET';
    case GROSS = 'GROSS';
    case SETTINGS = 'SETTINGS';
}