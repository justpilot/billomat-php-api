<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model\Enum;

/**
 * Gemeinsames `fromApi()` für die Enums des SDK.
 *
 * Billomat liefert leere Felder als "" und erweitert Wertemengen gelegentlich; beides wird
 * zu null statt einen `ValueError` zu werfen.
 *
 * @internal `fromApi()` auf den Enums ist öffentlich, der Trait selbst ist es nicht
 */
trait FromApi
{
    /**
     * Enum-Fall zum Billomat-Wert; null bei null, "" oder unbekanntem Wert.
     */
    public static function fromApi(?string $value): ?self
    {
        return null === $value || '' === $value ? null : self::tryFrom($value);
    }
}
