<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

/**
 * Typisierter Payload für POST /recurring-tags gemäß Billomat-API.
 *
 * Doku:
 * https://www.billomat.com/api/abo-rechnungen/schlagworte/
 *
 * Generiert aus tools/codegen/definitions/recurring-tags.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final class RecurringTagCreateOptions implements WriteOptions
{
    public function __construct(
        /**
         * ID des Datensatzes, an dem das Schlagwort hängt.
         *
         * Billomat-Feld: recurring_id
         * Typ: INT
         */
        public int $recurringId,
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
            'recurring_id' => $this->recurringId,
            'name' => $this->name,
        ];
    }
}
