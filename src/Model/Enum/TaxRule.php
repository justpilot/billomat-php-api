<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model\Enum;

enum TaxRule: string
{
    use FromApi;

    case TAX = 'TAX';
    case NO_TAX = 'NO_TAX';
    case COUNTRY = 'COUNTRY';
}
