<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use Justpilot\Billomat\Internal\ScalarCaster;

/**
 * Land aus der Billomat-Länderliste.
 *
 * Dokumentation:
 * https://www.billomat.com/en/api/countries/
 *
 * Generiert aus tools/codegen/definitions/countries.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final readonly class Country
{
    public function __construct(
        /** ISO-Ländercode (z. B. "DE"). */
        public string $code,
        /** Name des Landes. */
        public ?string $name = null,
        /** Interne Billomat-ID des Landes. */
        public ?int $id = null,
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
            code: ScalarCaster::toStringOrNull($data['code'] ?? $data['country_code'] ?? null) ?? '',
            name: ScalarCaster::toStringOrNull($data['name'] ?? null),
            id: ScalarCaster::toIntOrNull($data['id'] ?? null),
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
            'code' => $this->code,
            'name' => $this->name,
            'id' => $this->id,
        ];
    }
}
