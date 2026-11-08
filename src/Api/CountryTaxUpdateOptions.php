<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

/**
 * Typisierter Payload für PUT /country-taxes/{id} gemäß Billomat-API.
 *
 * Billomat verlangt `country_code` auch beim Ändern (sonst HTTP 400).
 *
 * Doku:
 * https://www.billomat.com/api/einstellungen/steuerfreie-laender/
 *
 * Generiert aus tools/codegen/definitions/country-taxes.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final readonly class CountryTaxUpdateOptions implements WriteOptions
{
    public function __construct(
        /**
         * Ländercode nach ISO 3166 Alpha-2.
         *
         * Beispiel: "CH"
         *
         * Billomat-Feld: country_code
         * Typ: ALNUM
         */
        public string|Clear|null $countryCode = null,
    ) {
    }

    /** Kopie mit geändertem `countryCode`. */
    public function withCountryCode(string|Clear|null $countryCode): self
    {
        return clone($this, ['countryCode' => $countryCode]);
    }

    /**
     * Serialisiert die Options in ein Billomat-Payload-Array.
     *
     * Nicht gesetzte Felder (null) fehlen im Payload, {@see Clear::Field} wird als null gesendet und leert das Feld.
     *
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return array_map(
            static fn (mixed $v): mixed => $v instanceof Clear ? null : $v,
            array_filter([
                'country_code' => $this->countryCode,
            ], static fn (mixed $v): bool => null !== $v),
        );
    }
}
