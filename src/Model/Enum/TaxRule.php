<?php

namespace Justpilot\Billomat\Model\Enum;

enum TaxRule: string
{
    case TAX = 'TAX';
    case NO_TAX = 'NO_TAX';
    case COUNTRY = 'COUNTRY';
}