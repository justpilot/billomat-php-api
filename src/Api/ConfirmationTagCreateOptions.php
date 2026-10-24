<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

/**
 * Typisierter Payload für POST /confirmation-tags gemäß Billomat-API.
 *
 * Doku:
 * https://www.billomat.com/en/api/confirmations/tags/
 *
 * Generiert aus tools/codegen/definitions/confirmation-tags.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final class ConfirmationTagCreateOptions
{
    public function __construct(
        /**
         * ID des Datensatzes, an dem das Schlagwort hängt.
         *
         * Billomat-Feld: confirmation_id
         */
        public int $confirmationId,
        /**
         * Name des Schlagworts.
         *
         * Billomat-Feld: name
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
            'confirmation_id' => $this->confirmationId,
            'name' => $this->name,
        ];
    }
}
