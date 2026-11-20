<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Internal;

/**
 * Hilfsklasse für verschachtelte Listen in Billomat-Responses
 * (z. B. `taxes.tax` oder `invoice-items.invoice-item`) und für kommaseparierte
 * Enum-Werte (z. B. `payment_types`) sowie Enum-Maps (z. B. `rights`).
 *
 * Billomat liefert eine Liste mit genau einem Eintrag als einzelnes Objekt
 * statt als Liste. Diese Klasse normalisiert beide Formen auf eine Liste und
 * überspringt Einträge, die kein Objekt sind.
 *
 * Diese Klasse ist {@internal}. Sie ist Teil der SDK-Implementation und
 * fällt nicht unter die BC-Garantie.
 *
 * @internal
 */
final class ListCaster
{
    /**
     * @template T
     *
     * @param string                           $singleKey Schlüssel, an dem ein einzelnes Objekt erkannt wird (z. B. 'id')
     * @param callable(array<string,mixed>): T $hydrate
     *
     * @return list<T>
     */
    public static function toList(mixed $value, string $singleKey, callable $hydrate): array
    {
        if (!\is_array($value)) {
            return [];
        }

        if (isset($value[$singleKey])) {
            $value = [$value];
        }

        $list = [];
        foreach ($value as $row) {
            if (\is_array($row)) {
                /** @var array<string,mixed> $row */
                $list[] = $hydrate($row);
            }
        }

        return $list;
    }

    /**
     * Wie {@see toList()}, liest die Liste aber aus einem Wrapper-Objekt
     * (z. B. `taxes` → `tax`). Ist der Wrapper kein Array (z. B. ein leerer String),
     * ergibt das eine leere Liste.
     *
     * @template T
     *
     * @param string                           $innerKey  Schlüssel der Liste im Wrapper (z. B. 'tax')
     * @param string                           $singleKey Schlüssel, an dem ein einzelnes Objekt erkannt wird (z. B. 'id')
     * @param callable(array<string,mixed>): T $hydrate
     *
     * @return list<T>
     */
    public static function toNestedList(mixed $wrapper, string $innerKey, string $singleKey, callable $hydrate): array
    {
        if (!\is_array($wrapper)) {
            return [];
        }

        return self::toList($wrapper[$innerKey] ?? null, $singleKey, $hydrate);
    }

    /**
     * Zerlegt einen kommaseparierten Billomat-String (z. B. "BANK_TRANSFER,CASH") in Enum-Fälle.
     *
     * Leerzeichen um die Werte werden entfernt; unbekannte und leere Werte fallen heraus.
     *
     * @template T of \BackedEnum
     *
     * @param class-string<T> $enum
     *
     * @return list<T>
     */
    public static function toEnumList(mixed $value, string $enum): array
    {
        $string = ScalarCaster::toStringOrNull($value);
        if (null === $string) {
            return [];
        }

        $list = [];
        foreach (explode(',', $string) as $part) {
            $case = $enum::tryFrom(trim($part));
            if (null !== $case) {
                $list[] = $case;
            }
        }

        return $list;
    }

    /**
     * Wandelt ein Billomat-Objekt mit Enum-Werten (z. B. `rights`: Bereich → `READ`/`UPDATE`/`DELETE`)
     * in eine Map aus Enum-Fällen.
     *
     * Einträge mit leerem oder unbekanntem Wert sowie nicht-String-Schlüssel fallen heraus;
     * ist der Wert kein Objekt (z. B. ein leerer String), ergibt das eine leere Map.
     *
     * @template T of \BackedEnum
     *
     * @param class-string<T> $enum
     *
     * @return array<string, T>
     */
    public static function toEnumMap(mixed $value, string $enum): array
    {
        if (!\is_array($value)) {
            return [];
        }

        $map = [];
        foreach ($value as $key => $raw) {
            $case = $enum::tryFrom(ScalarCaster::toStringOrNull($raw) ?? '');
            if (\is_string($key) && null !== $case) {
                $map[$key] = $case;
            }
        }

        return $map;
    }
}
