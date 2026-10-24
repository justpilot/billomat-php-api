<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use Justpilot\Billomat\Internal\ScalarCaster;

/**
 * Steuerfreies Land, für das Billomat keine Mehrwertsteuer berechnet.
 *
 * Dieses Model bildet die Struktur von GET /country-taxes bzw. GET /country-taxes/{id} ab.
 *
 * Dokumentation:
 * https://www.billomat.com/api/einstellungen/steuerfreie-laender/
 *
 * Generiert aus tools/codegen/definitions/country-taxes.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final readonly class CountryTax
{
    public function __construct(
        /** Interne Billomat-ID des Eintrags. */
        public ?int $id,
        /**
         * Ländercode nach ISO 3166 Alpha-2.
         *
         * Beispiel: "CH"
         */
        public ?string $countryCode = null,
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
            countryCode: ScalarCaster::toStringOrNull($data['country_code'] ?? null),
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
            'country_code' => $this->countryCode,
        ];
    }
}
