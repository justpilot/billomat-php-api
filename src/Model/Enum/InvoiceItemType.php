<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model\Enum;

/**
 * Typ der Rechnungsposition laut Billomat-Dokumentation.
 */
enum InvoiceItemType: string
{
    case PRODUCT = 'PRODUCT';
    case SERVICE = 'SERVICE';

    public static function fromApi(?string $value): ?self
    {
        return $value ? self::tryFrom($value) : null;
    }
}