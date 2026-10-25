<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use BcMath\Number;
use InvalidArgumentException;
use JsonSerializable;
use RoundingMode;
use Stringable;
use ValueError;

/**
 * Geldbetrag als exakte Dezimalzahl statt `float`.
 *
 * Billomat liefert und akzeptiert Beträge als Dezimal-Strings (`"33.61"`, `"40"`).
 * `Money` rechnet mit `BcMath\Number` ohne Rundungsfehler und ohne feste Nachkommastellen:
 * Billomat speichert Einzelpreise mit bis zu vier Stellen (`"12.3456"`).
 *
 * Die Währung steht nicht im Betrag, sondern am Dokument (`currencyCode`).
 *
 * ```php
 * $total = Money::sum(...array_map(static fn (Invoice $i): ?Money => $i->openAmount, $invoices));
 * $total->format(2, ',', '.'); // "1.234,50"
 *
 * new InvoiceItemCreateOptions(unitPrice: Money::of('19.99'), quantity: 3.0);
 * ```
 */
final readonly class Money implements JsonSerializable, Stringable
{
    private function __construct(
        private Number $amount,
    ) {
    }

    /**
     * Betrag aus einem Dezimal-String (`"19.99"`, `"-5"`) oder einer Ganzzahl.
     *
     * @throws InvalidArgumentException bei einem String, der keine Dezimalzahl ist (z. B. `"1e3"`, `"19,99"`)
     */
    public static function of(self|Number|string|int $amount): self
    {
        if ($amount instanceof self) {
            return $amount;
        }
        if ($amount instanceof Number) {
            return new self($amount);
        }

        // is_numeric() lässt noch "1e3" und " 1" durch; die lehnt BcMath\Number ab.
        if (\is_string($amount) && !is_numeric($amount)) {
            throw self::invalid($amount);
        }

        try {
            return new self(new Number($amount));
        } catch (ValueError) {
            throw self::invalid((string) $amount);
        }
    }

    private static function invalid(string $amount): InvalidArgumentException
    {
        return new InvalidArgumentException(\sprintf('Ungültiger Betrag "%s": erwartet wird eine Dezimalzahl mit Punkt, z. B. "19.99".', $amount));
    }

    public static function zero(): self
    {
        return new self(new Number(0));
    }

    /**
     * Summe aller Beträge; `null` zählt als 0 (z. B. ein fehlendes `openAmount`).
     */
    public static function sum(?self ...$amounts): self
    {
        $sum = new Number(0);
        foreach ($amounts as $amount) {
            if ($amount instanceof self) {
                $sum = $sum->add($amount->amount);
            }
        }

        return new self($sum);
    }

    public function add(self|string|int $other): self
    {
        return new self($this->amount->add(self::of($other)->amount));
    }

    public function subtract(self|string|int $other): self
    {
        return new self($this->amount->sub(self::of($other)->amount));
    }

    /**
     * Produkt, z. B. Einzelpreis × Menge. Das Ergebnis ist ungerundet, siehe {@see round()}.
     */
    public function multiply(self|string|int $factor): self
    {
        return new self($this->amount->mul(self::of($factor)->amount));
    }

    public function negate(): self
    {
        return new self($this->amount->mul(-1));
    }

    public function abs(): self
    {
        return $this->isNegative() ? $this->negate() : $this;
    }

    /**
     * Auf `$scale` Nachkommastellen gerundet, standardmäßig kaufmännisch (0.005 → 0.01).
     */
    public function round(int $scale = 2, RoundingMode $mode = RoundingMode::HalfAwayFromZero): self
    {
        return new self($this->amount->round($scale, $mode));
    }

    /**
     * -1, 0 oder 1, je nachdem ob der Betrag kleiner, gleich oder größer als `$other` ist.
     */
    public function compareTo(self|string|int $other): int
    {
        return $this->amount->compare(self::of($other)->amount);
    }

    /**
     * Wertgleichheit unabhängig von Nachkommastellen: `"40"` ist gleich `"40.00"`.
     */
    public function equals(self|string|int $other): bool
    {
        return 0 === $this->compareTo($other);
    }

    public function isGreaterThan(self|string|int $other): bool
    {
        return 1 === $this->compareTo($other);
    }

    public function isLessThan(self|string|int $other): bool
    {
        return -1 === $this->compareTo($other);
    }

    public function isZero(): bool
    {
        return 0 === $this->compareTo(0);
    }

    public function isPositive(): bool
    {
        return 1 === $this->compareTo(0);
    }

    public function isNegative(): bool
    {
        return -1 === $this->compareTo(0);
    }

    /**
     * Betrag mit fester Anzahl Nachkommastellen, kaufmännisch gerundet.
     *
     * `format()` → `"1234.50"`, `format(2, ',', '.')` → `"1.234,50"`.
     */
    public function format(int $decimals = 2, string $decimalSeparator = '.', string $thousandsSeparator = ''): string
    {
        // round() liefert genau `$decimals` Nachkommastellen, auch beim Auffüllen ("40" → "40.00").
        $value = $this->amount->round($decimals, RoundingMode::HalfAwayFromZero)->value;

        $negative = str_starts_with($value, '-');
        [$integer, $fraction] = explode('.', ltrim($value, '-')) + [1 => ''];
        if ('' !== $thousandsSeparator) {
            $integer = ltrim(strrev(chunk_split(strrev($integer), 3, strrev($thousandsSeparator))), $thousandsSeparator);
        }

        return ($negative ? '-' : '').$integer.('' !== $fraction ? $decimalSeparator.$fraction : '');
    }

    /**
     * Kürzeste exakte Darstellung ohne überflüssige Nullen, wie Billomat sie liefert: `"40"`, `"0.1"`, `"-12.3456"`.
     */
    public function toString(): string
    {
        $value = $this->amount->value;
        if (str_contains($value, '.')) {
            $value = rtrim(rtrim($value, '0'), '.');
        }

        return '-0' === $value ? '0' : $value;
    }

    public function toNumber(): Number
    {
        return $this->amount;
    }

    /**
     * Nur für Schnittstellen, die `float` erwarten – dabei geht Genauigkeit verloren.
     */
    public function toFloat(): float
    {
        return (float) $this->amount->value;
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    /**
     * Als String, damit JSON keine Rundungsfehler einführt.
     */
    public function jsonSerialize(): string
    {
        return $this->toString();
    }
}
