<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use Justpilot\Billomat\Internal\ScalarCaster;

/**
 * Steuerzeile eines Dokuments: die Summe der Steuer zu einem Steuersatz.
 *
 * Billomat liefert sie unter `taxes.tax` bei Rechnungen, Angeboten, Gutschriften,
 * Auftragsbestätigungen, Lieferscheinen und Abo-Rechnungen – eine Zeile pro Steuersatz,
 * mit Steuer-, Netto- und Bruttobetrag jeweils ungerundet (`*Plain`) und gerundet (`*Rounded`):
 *
 * ```php
 * foreach ($invoice->taxes as $tax) {
 *     printf('%s (%s %%): %s auf %s netto', $tax->name, $tax->rate, $tax->amount->format(2, ',', '.'), $tax->amountNetRounded?->format(2, ',', '.'));
 * }
 * ```
 */
final readonly class TaxSummary
{
    /** Billomat-Felder, die das Model abbildet. */
    private const array FIELDS = [
        'name' => true,
        'rate' => true,
        'amount' => true,
        'amount_plain' => true,
        'amount_rounded' => true,
        'amount_net' => true,
        'amount_net_plain' => true,
        'amount_net_rounded' => true,
        'amount_gross' => true,
        'amount_gross_plain' => true,
        'amount_gross_rounded' => true,
    ];

    /** @param array<string, mixed> $extra */
    public function __construct(
        /** Name des Steuersatzes, z. B. "MwSt". */
        public string $name,
        /** Steuersatz in Prozent, z. B. 19.0. */
        public float $rate,
        /** Steuerbetrag zu diesem Satz. */
        public Money $amount,
        /** Steuerbetrag ungerundet. */
        public ?Money $amountPlain = null,
        /** Steuerbetrag auf zwei Nachkommastellen gerundet. */
        public ?Money $amountRounded = null,
        /** Nettobetrag, auf den dieser Steuersatz angewendet wird. */
        public ?Money $amountNet = null,
        /** Nettobetrag ungerundet. */
        public ?Money $amountNetPlain = null,
        /** Nettobetrag auf zwei Nachkommastellen gerundet. */
        public ?Money $amountNetRounded = null,
        /** Bruttobetrag zu diesem Steuersatz (netto plus Steuer). */
        public ?Money $amountGross = null,
        /** Bruttobetrag ungerundet. */
        public ?Money $amountGrossPlain = null,
        /** Bruttobetrag auf zwei Nachkommastellen gerundet. */
        public ?Money $amountGrossRounded = null,
        /**
         * Felder, die Billomat liefert, das SDK aber (noch) nicht abbildet – roh, wie in der Response.
         *
         * Neue Billomat-Felder gehen so nicht verloren; toArray() gibt sie mit aus.
         */
        public array $extra = [],
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
            amountPlain: ScalarCaster::toMoneyOrNull($data['amount_plain'] ?? null),
            amountRounded: ScalarCaster::toMoneyOrNull($data['amount_rounded'] ?? null),
            amountNet: ScalarCaster::toMoneyOrNull($data['amount_net'] ?? null),
            amountNetPlain: ScalarCaster::toMoneyOrNull($data['amount_net_plain'] ?? null),
            amountNetRounded: ScalarCaster::toMoneyOrNull($data['amount_net_rounded'] ?? null),
            amountGross: ScalarCaster::toMoneyOrNull($data['amount_gross'] ?? null),
            amountGrossPlain: ScalarCaster::toMoneyOrNull($data['amount_gross_plain'] ?? null),
            amountGrossRounded: ScalarCaster::toMoneyOrNull($data['amount_gross_rounded'] ?? null),
            extra: array_diff_key($data, self::FIELDS),
        );
    }

    /**
     * Exportiert die Zeile mit Billomat-Feldnamen; Beträge sind Strings.
     *
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            ...$this->extra,
            'name' => $this->name,
            'rate' => $this->rate,
            'amount' => $this->amount->toString(),
            'amount_plain' => $this->amountPlain?->toString(),
            'amount_rounded' => $this->amountRounded?->toString(),
            'amount_net' => $this->amountNet?->toString(),
            'amount_net_plain' => $this->amountNetPlain?->toString(),
            'amount_net_rounded' => $this->amountNetRounded?->toString(),
            'amount_gross' => $this->amountGross?->toString(),
            'amount_gross_plain' => $this->amountGrossPlain?->toString(),
            'amount_gross_rounded' => $this->amountGrossRounded?->toString(),
        ];
    }
}
