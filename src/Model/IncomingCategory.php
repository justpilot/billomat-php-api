<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use Justpilot\Billomat\Internal\ScalarCaster;

/**
 * Kategorie einer Eingangsrechnung (z. B. „goods“, „services“).
 *
 * Dokumentation:
 * https://www.billomat.com/api/eingangsrechnungen/kategorien/
 *
 * Generiert aus tools/codegen/definitions/incoming-categories.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final readonly class IncomingCategory
{
    public function __construct(
        /** Kennung der Kategorie. */
        public string $id,
        /** Bezeichnung der Kategorie. */
        public ?string $title = null,
        /** Beschreibung der Kategorie. */
        public ?string $description = null,
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
            id: ScalarCaster::toStringOrNull($data['id'] ?? null) ?? '',
            title: ScalarCaster::toStringOrNull($data['title'] ?? null),
            description: ScalarCaster::toStringOrNull($data['description'] ?? null),
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
            'title' => $this->title,
            'description' => $this->description,
        ];
    }
}
