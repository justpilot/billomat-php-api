<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Internal;

use DateTimeImmutable;
use Throwable;

/**
 * Zentrale Hilfsklasse für die Hydration der Billomat-Response-Arrays.
 *
 * Billomat liefert in JSON-Responses an vielen Stellen leere Strings statt
 * echter NULL-Werte (z. B. `"reduction": ""` statt `"reduction": null`).
 * Außerdem werden Zahlen oft als String serialisiert. Diese Klasse bündelt
 * die wiederkehrenden Casts inkl. der "leerer-String = null"-Quirks an einer
 * einzigen Stelle, damit Model::fromArray() lesbar bleibt.
 *
 * Diese Klasse ist {@internal}. Sie ist Teil der SDK-Implementation und
 * fällt nicht unter die BC-Garantie.
 *
 * @internal
 */
final class ScalarCaster
{
    public static function toIntOrNull(mixed $value): ?int
    {
        if (null === $value || '' === $value) {
            return null;
        }

        if (\is_int($value)) {
            return $value;
        }

        if (\is_string($value) || \is_float($value) || \is_bool($value)) {
            return (int) $value;
        }

        return null;
    }

    public static function toFloatOrNull(mixed $value): ?float
    {
        if (null === $value || '' === $value) {
            return null;
        }

        if (\is_float($value) || \is_int($value)) {
            return (float) $value;
        }

        if (\is_string($value) || \is_bool($value)) {
            return (float) $value;
        }

        return null;
    }

    public static function toBoolOrNull(mixed $value): ?bool
    {
        if (null === $value || '' === $value) {
            return null;
        }

        if (\is_bool($value)) {
            return $value;
        }

        if (\is_int($value) || \is_float($value)) {
            return 0 !== (int) $value;
        }

        if (\is_string($value)) {
            // Billomat liefert teilweise "0"/"1" oder "true"/"false"
            return !\in_array(strtolower($value), ['0', 'false', 'off', 'no'], true);
        }

        return null;
    }

    public static function toStringOrNull(mixed $value): ?string
    {
        if (null === $value) {
            return null;
        }

        if (\is_string($value)) {
            return '' === $value ? null : $value;
        }

        if (\is_int($value) || \is_float($value) || \is_bool($value)) {
            return (string) $value;
        }

        return null;
    }

    /**
     * Parst Billomat-Datums-/Zeitwerte zu DateTimeImmutable.
     *
     * Akzeptiert sowohl reine Datumsstrings ("2026-06-02") als auch
     * ISO-8601-Zeitstempel ("2026-06-02T10:15:00+02:00"). Liefert null,
     * wenn der Wert leer oder nicht parsebar ist (statt Throwable
     * durchzureichen — Billomat liefert gelegentlich "0000-00-00" o. Ä.).
     */
    public static function toDateTimeOrNull(mixed $value): ?DateTimeImmutable
    {
        if (!\is_string($value) || '' === trim($value)) {
            return null;
        }

        try {
            return new DateTimeImmutable($value);
        } catch (Throwable) {
            return null;
        }
    }
}
