<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use Justpilot\Billomat\Internal\ScalarCaster;
use Justpilot\Billomat\Model\Enum\ItemType;

/**
 * Repräsentiert einen Posten einer Eingangsrechnung aus der Billomat-API.
 *
 * Dieses Model bildet die Struktur von GET /incoming-items?incoming_id={id} bzw. GET /incoming-items/{id} ab.
 *
 * Dokumentation:
 * https://www.billomat.com/api/eingangsrechnungen/posten/
 *
 * Generiert aus tools/codegen/definitions/incoming-items.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final readonly class IncomingItem
{
    /** Billomat-Felder, die das Model abbildet. */
    private const array FIELDS = [
        'id' => true,
        'incoming_id' => true,
        'total' => true,
        'description' => true,
        'position' => true,
        'tax_name' => true,
        'tax_rate' => true,
        'tax_rate_reverse_charge' => true,
        'tax_included' => true,
        'category' => true,
        'type' => true,
        'cost_center' => true,
        'expense_account_number' => true,
        'customfield' => true,
    ];

    /** @param array<string, mixed> $extra */
    public function __construct(
        /** Interne Billomat-ID des Postens. */
        public int $id,
        /** ID der zugehörigen Eingangsrechnung. */
        public ?int $incomingId = null,
        /** Betrag des Postens. */
        public ?Money $total = null,
        /** Beschreibung des Postens. */
        public ?string $description = null,
        /**
         * Laufende Nummer des Postens innerhalb der Eingangsrechnung.
         *
         * Wird beim Anlegen normalerweise automatisch vergeben (anhängen ans Ende).
         */
        public ?int $position = null,
        /** Steuerbezeichnung (z. B. "USt"). */
        public ?string $taxName = null,
        /** Steuerrate in Prozent. */
        public ?float $taxRate = null,
        /** Steuerrate für das Reverse-Charge-Verfahren. */
        public ?float $taxRateReverseCharge = null,
        /** Ist die Steuer im Betrag enthalten (Brutto)? */
        public ?bool $taxIncluded = null,
        /** Titel der Kategorie. */
        public ?string $category = null,
        /**
         * Produkt oder Dienstleistung.
         *
         * Nur bei Rechnungen ins Ausland, abhängig vom Firmenstandort.
         */
        public ?ItemType $type = null,
        /** Kostenstelle. */
        public ?int $costCenter = null,
        /** Nummer des Aufwandskontos. */
        public ?int $expenseAccountNumber = null,
        /** Freies Zusatzfeld (siehe Custom-Meta-Attribute). */
        public ?string $customField = null,
        /**
         * Felder, die Billomat liefert, das SDK aber (noch) nicht abbildet – roh, wie in der Response.
         *
         * Neue Billomat-Felder gehen so nicht verloren; toArray() gibt sie mit aus.
         */
        public array $extra = [],
    ) {
    }

    /**
     * Hydriert das Model aus einem Billomat-Response-Array.
     *
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: ScalarCaster::required(ScalarCaster::toIntOrNull($data['id'] ?? null), self::class, 'id'),
            incomingId: ScalarCaster::toIntOrNull($data['incoming_id'] ?? null),
            total: ScalarCaster::toMoneyOrNull($data['total'] ?? null),
            description: ScalarCaster::toStringOrNull($data['description'] ?? null),
            position: ScalarCaster::toIntOrNull($data['position'] ?? null),
            taxName: ScalarCaster::toStringOrNull($data['tax_name'] ?? null),
            taxRate: ScalarCaster::toFloatOrNull($data['tax_rate'] ?? null),
            taxRateReverseCharge: ScalarCaster::toFloatOrNull($data['tax_rate_reverse_charge'] ?? null),
            taxIncluded: ScalarCaster::toBoolOrNull($data['tax_included'] ?? null),
            category: ScalarCaster::toStringOrNull($data['category'] ?? null),
            type: ItemType::tryFrom(ScalarCaster::toStringOrNull($data['type'] ?? null) ?? ''),
            costCenter: ScalarCaster::toIntOrNull($data['cost_center'] ?? null),
            expenseAccountNumber: ScalarCaster::toIntOrNull($data['expense_account_number'] ?? null),
            customField: ScalarCaster::toStringOrNull($data['customfield'] ?? null),
            extra: array_diff_key($data, self::FIELDS),
        );
    }

    /**
     * Exportiert das Model als Array mit Billomat-Feldnamen.
     *
     * Praktisch für Debugging oder Log-Ausgaben.
     *
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            ...$this->extra,
            'id' => $this->id,
            'incoming_id' => $this->incomingId,
            'total' => $this->total?->toString(),
            'description' => $this->description,
            'position' => $this->position,
            'tax_name' => $this->taxName,
            'tax_rate' => $this->taxRate,
            'tax_rate_reverse_charge' => $this->taxRateReverseCharge,
            'tax_included' => $this->taxIncluded,
            'category' => $this->category,
            'type' => $this->type?->value,
            'cost_center' => $this->costCenter,
            'expense_account_number' => $this->expenseAccountNumber,
            'customfield' => $this->customField,
        ];
    }
}
