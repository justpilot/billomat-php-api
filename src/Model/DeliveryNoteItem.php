<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use Justpilot\Billomat\Internal\ScalarCaster;
use Justpilot\Billomat\Model\Enum\ItemType;

/**
 * Repräsentiert eine Lieferscheinposition aus der Billomat-API.
 *
 * Dieses Model bildet die Struktur von GET /delivery-note-items?delivery_note_id={id} bzw. GET /delivery-note-items/{id} ab.
 *
 * Dokumentation:
 * https://www.billomat.com/api/lieferscheine/
 *
 * Generiert aus tools/codegen/definitions/delivery-note-items.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final readonly class DeliveryNoteItem
{
    /** Billomat-Felder, die das Model abbildet. */
    private const array FIELDS = [
        'id' => true,
        'delivery_note_id' => true,
        'article_id' => true,
        'position' => true,
        'unit' => true,
        'quantity' => true,
        'tax_changed_manually' => true,
        'title' => true,
        'description' => true,
        'type' => true,
        'customfield' => true,
    ];

    /** @param array<string, mixed> $extra */
    public function __construct(
        /** Interne Billomat-ID der Position. */
        public int $id,
        /** ID des zugehörigen Dokuments. */
        public ?int $deliveryNoteId = null,
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
        /**
         * Flag, ob die Steuerrate manuell geändert wurde.
         *
         * Laut Doku Pflicht, *wenn* nicht der Standard-Steuersatz angewendet werden soll.
         */
        public ?bool $taxChangedManually = null,
        /** Titel der Position. */
        public ?string $title = null,
        /** Beschreibung der Position. */
        public ?string $description = null,
        /** Typ der Position (Produkt/Dienstleistung). */
        public ?ItemType $type = null,
        /** Freies Zusatzfeld (siehe Custom-Meta-Attribute). */
        public ?string $customfield = null,
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
            deliveryNoteId: ScalarCaster::toIntOrNull($data['delivery_note_id'] ?? null),
            articleId: ScalarCaster::toIntOrNull($data['article_id'] ?? null),
            position: ScalarCaster::toIntOrNull($data['position'] ?? null),
            unit: ScalarCaster::toStringOrNull($data['unit'] ?? null),
            quantity: ScalarCaster::toFloatOrNull($data['quantity'] ?? null) ?? 0.0,
            taxChangedManually: ScalarCaster::toBoolOrNull($data['tax_changed_manually'] ?? null),
            title: ScalarCaster::toStringOrNull($data['title'] ?? null),
            description: ScalarCaster::toStringOrNull($data['description'] ?? null),
            type: ItemType::tryFrom(ScalarCaster::toStringOrNull($data['type'] ?? null) ?? ''),
            customfield: ScalarCaster::toStringOrNull($data['customfield'] ?? null),
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
            'delivery_note_id' => $this->deliveryNoteId,
            'article_id' => $this->articleId,
            'position' => $this->position,
            'unit' => $this->unit,
            'quantity' => $this->quantity,
            'tax_changed_manually' => $this->taxChangedManually,
            'title' => $this->title,
            'description' => $this->description,
            'type' => $this->type?->value,
            'customfield' => $this->customfield,
        ];
    }
}
