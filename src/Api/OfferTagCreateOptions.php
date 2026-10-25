<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

/**
 * Typisierter Payload für POST /offer-tags gemäß Billomat-API.
 *
 * Doku:
 * https://www.billomat.com/api/angebote/schlagworte/
 *
 * Generiert aus tools/codegen/definitions/offer-tags.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final class OfferTagCreateOptions implements WriteOptions
{
    public function __construct(
        /**
         * ID des Datensatzes, an dem das Schlagwort hängt.
         *
         * Billomat-Feld: offer_id
         * Typ: INT
         */
        public int $offerId,
        /**
         * Name des Schlagworts.
         *
         * Billomat-Feld: name
         * Typ: ALNUM
         */
        public string $name,
    ) {
    }

    /**
     * Serialisiert die Options in ein Billomat-Payload-Array.
     *
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'offer_id' => $this->offerId,
            'name' => $this->name,
        ];
    }
}
