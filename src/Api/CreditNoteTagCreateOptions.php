<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

/**
 * Typisierter Payload für POST /credit-note-tags gemäß Billomat-API.
 *
 * Doku:
 * https://www.billomat.com/api/gutschriften/schlagworte/
 *
 * Generiert aus tools/codegen/definitions/credit-note-tags.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final readonly class CreditNoteTagCreateOptions implements WriteOptions
{
    public function __construct(
        /**
         * Name des Schlagworts.
         *
         * Billomat-Feld: name
         * Typ: ALNUM
         */
        public string $name,
    ) {
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
            'name' => $this->name,
        ];
    }
}
