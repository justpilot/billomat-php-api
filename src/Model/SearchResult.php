<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use Justpilot\Billomat\Internal\ScalarCaster;

/**
 * Ein einzelner Treffer der Billomat-Volltextsuche.
 *
 * Bildet ein `<result>`-Element aus der Antwort von GET /search ab. Das
 * SDK normalisiert `subline` (offizielle Doku schreibt dort fälschlich
 * `sublineline`; die API liefert `subline`).
 *
 * Dokumentation: https://www.billomat.com/api/suche/
 */
final readonly class SearchResult
{
    public function __construct(
        /** Slug der Ressource, z. B. "invoices", "delivery-notes". */
        public string $resource,
        /** ID des Treffers innerhalb der Ressource. */
        public ?int $id,
        /** Überschrift des Treffers, z. B. die Belegnummer. */
        public ?string $headline,
        /** Unterzeile, z. B. Datum und Kundenname. */
        public ?string $subline,
    ) {
    }

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            resource: ScalarCaster::toStringOrNull($data['resource'] ?? null) ?? '',
            id: ScalarCaster::toIntOrNull($data['id'] ?? null),
            headline: ScalarCaster::toStringOrNull($data['headline'] ?? null),
            subline: ScalarCaster::toStringOrNull($data['subline'] ?? null),
        );
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'resource' => $this->resource,
            'id' => $this->id,
            'headline' => $this->headline,
            'subline' => $this->subline,
        ];
    }
}
