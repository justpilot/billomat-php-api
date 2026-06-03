<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model\Enum;

/**
 * Typ eines Artikels laut Billomat-Dokumentation (Produkt oder Dienstleistung).
 */
enum ArticleType: string
{
    case PRODUCT = 'PRODUCT';
    case SERVICE = 'SERVICE';

    public static function fromApi(?string $value): ?self
    {
        return null === $value || '' === $value ? null : self::tryFrom($value);
    }
}
