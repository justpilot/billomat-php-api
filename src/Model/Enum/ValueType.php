<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model\Enum;

enum ValueType: string
{
    case SETTINGS = 'SETTINGS';
    case ABSOLUTE = 'ABSOLUTE';
    case RELATIVE = 'RELATIVE';
}