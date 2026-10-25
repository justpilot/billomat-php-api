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
    /** Billomat-Felder, die das Model abbildet. */
    private const array FIELDS = [
        'id' => true,
        'title' => true,
        'description' => true,
    ];

    /** @param array<string, mixed> $extra */
    public function __construct(
        /** Kennung der Kategorie. */
        public string $id,
        /** Bezeichnung der Kategorie. */
        public ?string $title = null,
        /** Beschreibung der Kategorie. */
        public ?string $description = null,
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
            id: ScalarCaster::toStringOrNull($data['id'] ?? null) ?? '',
            title: ScalarCaster::toStringOrNull($data['title'] ?? null),
            description: ScalarCaster::toStringOrNull($data['description'] ?? null),
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
            'title' => $this->title,
            'description' => $this->description,
        ];
    }
}
