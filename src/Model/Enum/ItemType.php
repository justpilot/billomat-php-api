<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model\Enum;

/**
 * Typ einer Dokumentposition (Rechnung, Angebot, Gutschrift, Auftragsbestätigung,
 * Lieferschein, Abo-Rechnung) laut Billomat-Dokumentation.
 */
enum ItemType: string
{
    case PRODUCT = 'PRODUCT';
    case SERVICE = 'SERVICE';

    public static function fromApi(?string $value): ?self
    {
        return $value ? self::tryFrom($value) : null;
    }
}
