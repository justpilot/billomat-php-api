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
 * Es ist als Read-Model gedacht: Anlegen und Ändern erfolgen über TaxRateCreateOptions.
 *
 * Dokumentation:
 * https://www.billomat.com/api/einstellungen/steuersaetze/
 *
 * Generiert aus tools/codegen/definitions/taxes.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final readonly class TaxRate
{
    public function __construct(
        /** Interne Billomat-ID des Steuersatzes. */
        public ?int $id,
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
        /** Erstellungszeitpunkt des Steuersatzes. */
        public ?DateTimeImmutable $created = null,
        /** Zeitpunkt der letzten Änderung. */
        public ?DateTimeImmutable $updated = null,
        /** Freies Zusatzfeld (siehe Custom-Meta-Attribute). */
        public ?string $customfield = null,
        /**
         * Kann der Steuersatz gelöscht werden?
         *
         * false, solange er auf Belegen oder Artikeln verwendet wird.
         */
        public ?bool $canDelete = null,
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
            id: ScalarCaster::toIntOrNull($data['id'] ?? null),
            name: ScalarCaster::toStringOrNull($data['name'] ?? null) ?? '',
            rate: ScalarCaster::toFloatOrNull($data['rate'] ?? null) ?? 0.0,
            isDefault: ScalarCaster::toBoolOrNull($data['is_default'] ?? null) ?? false,
            created: ScalarCaster::toDateTimeOrNull($data['created'] ?? null),
            updated: ScalarCaster::toDateTimeOrNull($data['updated'] ?? null),
            customfield: ScalarCaster::toStringOrNull($data['customfield'] ?? null),
            canDelete: ScalarCaster::toBoolOrNull($data['can_delete'] ?? null),
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
            'id' => $this->id,
            'name' => $this->name,
            'rate' => $this->rate,
            'is_default' => ($this->isDefault ? 1 : 0),
            'created' => $this->created?->format(DATE_ATOM),
            'updated' => $this->updated?->format(DATE_ATOM),
            'customfield' => $this->customfield,
            'can_delete' => null === $this->canDelete ? null : ($this->canDelete ? 1 : 0),
        ];
    }
}
