<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use DateTimeImmutable;
use Justpilot\Billomat\Internal\ScalarCaster;

use const DATE_ATOM;

/**
 * Repräsentiert einen Steuersatz aus der Billomat-API.
 *
 * Dieses Model bildet die Struktur von GET /taxes bzw. GET /taxes/{id} ab.
 * Es ist als Read-Model gedacht: Anlegen über TaxRateCreateOptions, Ändern über TaxRateUpdateOptions.
 *
 * Dokumentation:
 * https://www.billomat.com/api/einstellungen/steuersaetze/
 *
 * Generiert aus tools/codegen/definitions/taxes.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final readonly class TaxRate
{
    /** Billomat-Felder, die das Model abbildet. */
    private const array FIELDS = [
        'id' => true,
        'name' => true,
        'rate' => true,
        'is_default' => true,
        'vat_category_code' => true,
        'vat_exemption_reason_code' => true,
        'vat_exemption_reason_text' => true,
        'created' => true,
        'updated' => true,
        'customfield' => true,
        'can_delete' => true,
    ];

    /** @param array<string, mixed> $extra */
    public function __construct(
        /** Interne Billomat-ID des Steuersatzes. */
        public int $id,
        /**
         * Name des Steuersatzes.
         *
         * Beispiel: "MwSt"
         */
        public string $name,
        /**
         * Höhe des Steuersatzes in Prozent.
         *
         * Beispiel: 19.0
         */
        public float $rate,
        /** Standard-Steuersatz? */
        public bool $isDefault,
        /**
         * Umsatzsteuer-Kategorie für E-Rechnungen (Codeliste UNCL5305, z. B. "S", "Z", "E", "AE").
         *
         * Von Billomat geliefert und angenommen, aber nicht dokumentiert.
         */
        public ?string $vatCategoryCode = null,
        /**
         * Code des Befreiungsgrunds für E-Rechnungen (Codeliste VATEX, z. B. "VATEX-EU-132").
         *
         * Von Billomat geliefert und angenommen, aber nicht dokumentiert.
         */
        public ?string $vatExemptionReasonCode = null,
        /**
         * Befreiungsgrund als Text für E-Rechnungen.
         *
         * Von Billomat geliefert und angenommen, aber nicht dokumentiert.
         */
        public ?string $vatExemptionReasonText = null,
        /** Erstellungszeitpunkt des Steuersatzes. */
        public ?DateTimeImmutable $created = null,
        /** Zeitpunkt der letzten Änderung. */
        public ?DateTimeImmutable $updated = null,
        /** Freies Zusatzfeld (siehe Custom-Meta-Attribute). */
        public ?string $customField = null,
        /**
         * Kann der Steuersatz gelöscht werden?
         *
         * false, solange er auf Belegen oder Artikeln verwendet wird.
         */
        public ?bool $canDelete = null,
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
            name: ScalarCaster::required(ScalarCaster::toStringOrNull($data['name'] ?? null), self::class, 'name'),
            rate: ScalarCaster::required(ScalarCaster::toFloatOrNull($data['rate'] ?? null), self::class, 'rate'),
            isDefault: ScalarCaster::required(ScalarCaster::toBoolOrNull($data['is_default'] ?? null), self::class, 'is_default'),
            vatCategoryCode: ScalarCaster::toStringOrNull($data['vat_category_code'] ?? null),
            vatExemptionReasonCode: ScalarCaster::toStringOrNull($data['vat_exemption_reason_code'] ?? null),
            vatExemptionReasonText: ScalarCaster::toStringOrNull($data['vat_exemption_reason_text'] ?? null),
            created: ScalarCaster::toDateTimeOrNull($data['created'] ?? null),
            updated: ScalarCaster::toDateTimeOrNull($data['updated'] ?? null),
            customField: ScalarCaster::toStringOrNull($data['customfield'] ?? null),
            canDelete: ScalarCaster::toBoolOrNull($data['can_delete'] ?? null),
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
            'name' => $this->name,
            'rate' => $this->rate,
            'is_default' => ($this->isDefault ? 1 : 0),
            'vat_category_code' => $this->vatCategoryCode,
            'vat_exemption_reason_code' => $this->vatExemptionReasonCode,
            'vat_exemption_reason_text' => $this->vatExemptionReasonText,
            'created' => $this->created?->format(DATE_ATOM),
            'updated' => $this->updated?->format(DATE_ATOM),
            'customfield' => $this->customField,
            'can_delete' => null === $this->canDelete ? null : ($this->canDelete ? 1 : 0),
        ];
    }
}
