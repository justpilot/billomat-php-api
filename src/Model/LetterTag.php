<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use Justpilot\Billomat\Internal\ScalarCaster;

/**
 * Schlagwort (Tag) an einem Brief.
 *
 * Dieses Model bildet die Struktur von GET /letter-tags?letter_id={id} bzw. GET /letter-tags/{id} ab.
 *
 * Dokumentation:
 * https://www.billomat.com/api/briefe/
 *
 * Generiert aus tools/codegen/definitions/letter-tags.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final readonly class LetterTag
{
    public function __construct(
        /** Interne Billomat-ID des Schlagworts. */
        public ?int $id,
        /** ID des Datensatzes, an dem das Schlagwort hängt. */
        public int $letterId,
        /** Name des Schlagworts. */
        public string $name,
        /** Freies Zusatzfeld (siehe Custom-Meta-Attribute). */
        public ?string $customfield = null,
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
            letterId: ScalarCaster::toIntOrNull($data['letter_id'] ?? null) ?? 0,
            name: ScalarCaster::toStringOrNull($data['name'] ?? null) ?? '',
            customfield: ScalarCaster::toStringOrNull($data['customfield'] ?? null),
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
            'letter_id' => $this->letterId,
            'name' => $this->name,
            'customfield' => $this->customfield,
        ];
    }
}
