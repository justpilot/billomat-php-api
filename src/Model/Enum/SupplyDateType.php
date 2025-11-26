<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model\Enum;

enum SupplyDateType: string
{
    case SUPPLY_DATE = 'SUPPLY_DATE';
    case DELIVERY_DATE = 'DELIVERY_DATE';
    case SUPPLY_TEXT = 'SUPPLY_TEXT';
    case DELIVERY_TEXT = 'DELIVERY_TEXT';
}