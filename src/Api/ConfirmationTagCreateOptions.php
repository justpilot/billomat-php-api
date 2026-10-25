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
final readonly class ConfirmationTagCreateOptions implements WriteOptions
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

    /** Kopie mit geändertem `confirmationId`. */
    public function withConfirmationId(int $confirmationId): self
    {
        return clone($this, ['confirmationId' => $confirmationId]);
    }

    /** Kopie mit geändertem `name`. */
    public function withName(string $name): self
    {
        return clone($this, ['name' => $name]);
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
