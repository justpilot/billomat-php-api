<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use Justpilot\Billomat\Internal\ScalarCaster;

/**
 * Steuerzeile eines Dokuments: die Summe der Steuer zu einem Steuersatz.
 *
 * Billomat liefert sie unter `taxes.tax` bei Rechnungen, Angeboten, Gutschriften,
 * Auftragsbestätigungen, Lieferscheinen und Abo-Rechnungen – eine Zeile pro Steuersatz:
 *
 * ```php
 * foreach ($invoice->taxes as $tax) {
 *     printf('%s (%s %%): %s', $tax->name, $tax->rate, $tax->amount->format(2, ',', '.'));
 * }
 * ```
 */
final readonly class TaxSummary
{
    public function __construct(
        /** Name des Steuersatzes, z. B. "MwSt". */
        public string $name,
        /** Steuersatz in Prozent, z. B. 19.0. */
        public float $rate,
        /** Steuerbetrag zu diesem Satz. */
        public Money $amount,
    ) {
    }

    /**
     * Hydriert die Zeile aus einem Eintrag von `taxes.tax`.
     *
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: ScalarCaster::toStringOrNull($data['name'] ?? null) ?? '',
            rate: ScalarCaster::toFloatOrNull($data['rate'] ?? null) ?? 0.0,
            amount: ScalarCaster::toMoneyOrNull($data['amount'] ?? null) ?? Money::zero(),
        );
    }

    /**
     * Exportiert die Zeile mit Billomat-Feldnamen; der Betrag ist ein String.
     *
     * @return array{name: string, rate: float, amount: string}
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'rate' => $this->rate,
            'amount' => $this->amount->toString(),
        ];
    }
}
