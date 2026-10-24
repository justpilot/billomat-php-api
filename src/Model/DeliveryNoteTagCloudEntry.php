<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use Justpilot\Billomat\Internal\ScalarCaster;

/**
 * Eintrag aus der aggregierten Tag-Cloud GET /delivery-note-tags (ohne delivery_note_id).
 *
 * Jedes Schlagwort wird einmal mit seiner Häufigkeit geliefert.
 *
 * Dokumentation:
 * https://www.billomat.com/api/lieferscheine/
 *
 * Generiert aus tools/codegen/definitions/delivery-note-tags.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final readonly class DeliveryNoteTagCloudEntry
{
    public function __construct(
        /** Interne Billomat-ID des Schlagworts. */
        public ?int $id,
        /** Name des Schlagworts. */
        public string $name,
        /** Häufigkeit des Schlagworts. */
        public int $count,
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
            count: ScalarCaster::toIntOrNull($data['count'] ?? null) ?? 0,
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
            'count' => $this->count,
        ];
    }
}
