<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use DateTimeImmutable;
use Justpilot\Billomat\Internal\ScalarCaster;

use const DATE_ATOM;

/**
 * Repräsentiert eine Mahnungsposition aus der Billomat-API.
 *
 * Dieses Model bildet die Struktur von GET /reminder-items?reminder_id={id} bzw. GET /reminder-items/{id} ab.
 *
 * Dokumentation:
 * https://www.billomat.com/api/mahnungen/positionen/
 *
 * Generiert aus tools/codegen/definitions/reminder-items.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final readonly class ReminderItem
{
    public function __construct(
        /** Interne Billomat-ID der Position. */
        public ?int $id,
        /** ID des zugehörigen Dokuments. */
        public ?int $reminderId,
        /** ID des Artikels (falls verknüpft). */
        public ?int $articleId,
        /**
         * Laufende Positionsnummer innerhalb des Dokuments.
         *
         * Wird beim Anlegen normalerweise automatisch vergeben (anhängen ans Ende).
         */
        public ?int $position,
        /** Einheit (z. B. "Stück", "Stunde"). */
        public ?string $unit,
        /** Menge. */
        public float $quantity,
        /** Einzelpreis. */
        public float $unitPrice,
        /** Titel der Position. */
        public ?string $title,
        /** Beschreibung der Position. */
        public ?string $description,
        /**
         * Gesamtbetrag brutto.
         *
         * @deprecated since 3.1 – Billomat liefert `total_gross` nicht (mehr) aus (Stand 2026-10), der Wert ist immer `null`. Wird in 4.0 entfernt.
         */
        public ?float $totalGross,
        /**
         * Gesamtbetrag netto.
         *
         * @deprecated since 3.1 – Billomat liefert `total_net` nicht (mehr) aus (Stand 2026-10), der Wert ist immer `null`. Wird in 4.0 entfernt.
         */
        public ?float $totalNet,
        /**
         * Erstellungszeitpunkt der Position.
         *
         * @deprecated since 3.1 – Billomat liefert `created` nicht (mehr) aus (Stand 2026-10), der Wert ist immer `null`. Wird in 4.0 entfernt.
         */
        public ?DateTimeImmutable $created,
        /** Freies Zusatzfeld (siehe Custom-Meta-Attribute). */
        public ?string $customfield = null,
        /** Gesamtbetrag der Position. */
        public ?float $total = null,
        /** Mahngebühr statt Artikelposition? */
        public ?bool $isCharge = null,
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
            reminderId: ScalarCaster::toIntOrNull($data['reminder_id'] ?? null),
            articleId: ScalarCaster::toIntOrNull($data['article_id'] ?? null),
            position: ScalarCaster::toIntOrNull($data['position'] ?? null),
            unit: ScalarCaster::toStringOrNull($data['unit'] ?? null),
            quantity: ScalarCaster::toFloatOrNull($data['quantity'] ?? null) ?? 0.0,
            unitPrice: ScalarCaster::toFloatOrNull($data['unit_price'] ?? null) ?? 0.0,
            title: ScalarCaster::toStringOrNull($data['title'] ?? null),
            description: ScalarCaster::toStringOrNull($data['description'] ?? null),
            totalGross: ScalarCaster::toFloatOrNull($data['total_gross'] ?? null),
            totalNet: ScalarCaster::toFloatOrNull($data['total_net'] ?? null),
            created: ScalarCaster::toDateTimeOrNull($data['created'] ?? null),
            customfield: ScalarCaster::toStringOrNull($data['customfield'] ?? null),
            total: ScalarCaster::toFloatOrNull($data['total'] ?? null),
            isCharge: ScalarCaster::toBoolOrNull($data['is_charge'] ?? null),
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
            'reminder_id' => $this->reminderId,
            'article_id' => $this->articleId,
            'position' => $this->position,
            'unit' => $this->unit,
            'quantity' => $this->quantity,
            'unit_price' => $this->unitPrice,
            'title' => $this->title,
            'description' => $this->description,
            'total_gross' => $this->totalGross,
            'total_net' => $this->totalNet,
            'created' => $this->created?->format(DATE_ATOM),
            'customfield' => $this->customfield,
            'total' => $this->total,
            'is_charge' => $this->isCharge,
        ];
    }
}
