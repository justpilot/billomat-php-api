<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

/**
 * Typisierter Payload für POST /country-taxes gemäß Billomat-API.
 *
 * Doku:
 * https://www.billomat.com/api/einstellungen/steuerfreie-laender/
 *
 * Generiert aus tools/codegen/definitions/country-taxes.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final readonly class CountryTaxCreateOptions implements WriteOptions
{
    public function __construct(
        /**
         * Ländercode nach ISO 3166 Alpha-2.
         *
         * Beispiel: "CH"
         *
         * Billomat-Feld: country_code
         * Typ: ALNUM
         * Default: Ländercode nach ISO 3166 Alpha-2
         */
        public string $countryCode,
    ) {
    }

    /** Kopie mit geändertem `countryCode`. */
    public function withCountryCode(string $countryCode): self
    {
        return clone($this, ['countryCode' => $countryCode]);
    }

    /**
     * Serialisiert die Options in ein Billomat-Payload-Array.
     *
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'country_code' => $this->countryCode,
        ];
    }
}
