<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use InvalidArgumentException;

/**
 * Sortierung einer Billomat-Liste (Query-Parameter `order_by`).
 *
 * Billomat erwartet `feld+RICHTUNG`, mehrere Sortierungen kommasepariert, z. B.
 * `order_by=date+DESC,invoice_number+ASC`. Das `+` bleibt im Query wörtlich
 * erhalten (siehe {@see \Justpilot\Billomat\Http\BillomatHttpClient}).
 *
 * ```php
 * OrderBy::desc('date')->thenAsc('invoice_number');
 * ```
 *
 * Doku: https://www.billomat.com/en/api/basics/read-data/
 */
final readonly class OrderBy
{
    /**
     * @param non-empty-list<array{string, 'ASC'|'DESC'}> $parts
     */
    private function __construct(
        private array $parts,
    ) {
    }

    /** Aufsteigend nach `$field` sortieren. */
    public static function asc(string $field): self
    {
        return new self([[self::field($field), 'ASC']]);
    }

    /** Absteigend nach `$field` sortieren. */
    public static function desc(string $field): self
    {
        return new self([[self::field($field), 'DESC']]);
    }

    /** Kopie mit einer zusätzlichen, aufsteigenden Sortierung. */
    public function thenAsc(string $field): self
    {
        return new self([...$this->parts, [self::field($field), 'ASC']]);
    }

    /** Kopie mit einer zusätzlichen, absteigenden Sortierung. */
    public function thenDesc(string $field): self
    {
        return new self([...$this->parts, [self::field($field), 'DESC']]);
    }

    /** Wert für `order_by`, z. B. `date+DESC,invoice_number+ASC`. */
    public function toQuery(): string
    {
        return implode(',', array_map(static fn (array $part): string => $part[0].'+'.$part[1], $this->parts));
    }

    private static function field(string $field): string
    {
        if (1 !== preg_match('/^[a-z][a-z0-9_]*$/', $field)) {
            throw new InvalidArgumentException(\sprintf('Ungültiges Sortierfeld "%s": erwartet wird ein Billomat-Feldname in snake_case.', $field));
        }

        return $field;
    }
}
