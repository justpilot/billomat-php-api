<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use Justpilot\Billomat\Internal\ScalarCaster;

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
        public Money $unitPrice,
        /** Titel der Position. */
        public ?string $title,
        /** Beschreibung der Position. */
        public ?string $description,
        /** Freies Zusatzfeld (siehe Custom-Meta-Attribute). */
        public ?string $customfield = null,
        /** Gesamtbetrag der Position. */
        public ?Money $total = null,
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
            unitPrice: ScalarCaster::toMoneyOrNull($data['unit_price'] ?? null) ?? Money::zero(),
            title: ScalarCaster::toStringOrNull($data['title'] ?? null),
            description: ScalarCaster::toStringOrNull($data['description'] ?? null),
            customfield: ScalarCaster::toStringOrNull($data['customfield'] ?? null),
            total: ScalarCaster::toMoneyOrNull($data['total'] ?? null),
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
            'unit_price' => $this->unitPrice->toString(),
            'title' => $this->title,
            'description' => $this->description,
            'customfield' => $this->customfield,
            'total' => $this->total?->toString(),
            'is_charge' => $this->isCharge,
        ];
    }
}
