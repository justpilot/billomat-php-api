<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

/**
 * Typisierter Payload für POST /delivery-note-tags gemäß Billomat-API.
 *
 * Doku:
 * https://www.billomat.com/api/lieferscheine/
 *
 * Generiert aus tools/codegen/definitions/delivery-note-tags.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final readonly class DeliveryNoteTagCreateOptions implements WriteOptions
{
    public function __construct(
        /**
         * ID des Datensatzes, an dem das Schlagwort hängt.
         *
         * Billomat-Feld: delivery_note_id
         */
        public int $deliveryNoteId,
        /**
         * Name des Schlagworts.
         *
         * Billomat-Feld: name
         */
        public string $name,
    ) {
    }

    /** Kopie mit geändertem `deliveryNoteId`. */
    public function withDeliveryNoteId(int $deliveryNoteId): self
    {
        return clone($this, ['deliveryNoteId' => $deliveryNoteId]);
    }

    /** Kopie mit geändertem `name`. */
    public function withName(string $name): self
    {
        return clone($this, ['name' => $name]);
    }

    /**
     * Serialisiert die Options in ein Billomat-Payload-Array.
     *
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'delivery_note_id' => $this->deliveryNoteId,
            'name' => $this->name,
        ];
    }
}
