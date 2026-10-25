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
    /** Billomat-Felder, die das Model abbildet. */
    private const array FIELDS = [
        'id' => true,
        'reminder_id' => true,
        'article_id' => true,
        'position' => true,
        'unit' => true,
        'quantity' => true,
        'unit_price' => true,
        'title' => true,
        'description' => true,
        'customfield' => true,
        'total' => true,
        'is_charge' => true,
    ];

    /** @param array<string, mixed> $extra */
    public function __construct(
        /** Interne Billomat-ID der Position. */
        public int $id,
        /** Einzelpreis. */
        public Money $unitPrice,
        /** ID des zugehörigen Dokuments. */
        public ?int $reminderId = null,
        /** ID des Artikels (falls verknüpft). */
        public ?int $articleId = null,
        /**
         * Laufende Positionsnummer innerhalb des Dokuments.
         *
         * Wird beim Anlegen normalerweise automatisch vergeben (anhängen ans Ende).
         */
        public ?int $position = null,
        /** Einheit (z. B. "Stück", "Stunde"). */
        public ?string $unit = null,
        /** Menge. */
        public float $quantity = 0.0,
        /** Titel der Position. */
        public ?string $title = null,
        /** Beschreibung der Position. */
        public ?string $description = null,
        /** Freies Zusatzfeld (siehe Custom-Meta-Attribute). */
        public ?string $customfield = null,
        /** Gesamtbetrag der Position. */
        public ?Money $total = null,
        /** Mahngebühr statt Artikelposition? */
        public ?bool $isCharge = null,
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
            unitPrice: ScalarCaster::required(ScalarCaster::toMoneyOrNull($data['unit_price'] ?? null), self::class, 'unit_price'),
            reminderId: ScalarCaster::toIntOrNull($data['reminder_id'] ?? null),
            articleId: ScalarCaster::toIntOrNull($data['article_id'] ?? null),
            position: ScalarCaster::toIntOrNull($data['position'] ?? null),
            unit: ScalarCaster::toStringOrNull($data['unit'] ?? null),
            quantity: ScalarCaster::toFloatOrNull($data['quantity'] ?? null) ?? 0.0,
            title: ScalarCaster::toStringOrNull($data['title'] ?? null),
            description: ScalarCaster::toStringOrNull($data['description'] ?? null),
            customfield: ScalarCaster::toStringOrNull($data['customfield'] ?? null),
            total: ScalarCaster::toMoneyOrNull($data['total'] ?? null),
            isCharge: ScalarCaster::toBoolOrNull($data['is_charge'] ?? null),
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
