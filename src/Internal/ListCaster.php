<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Internal;

/**
 * Hilfsklasse für verschachtelte Listen in Billomat-Responses
 * (z. B. `taxes.tax` oder `invoice-items.invoice-item`).
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
}
