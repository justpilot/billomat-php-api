<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

/**
 * Typisierter Payload für POST /letter-tags gemäß Billomat-API.
 *
 * Doku:
 * https://www.billomat.com/api/briefe/
 *
 * Generiert aus tools/codegen/definitions/letter-tags.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final readonly class LetterTagCreateOptions implements WriteOptions
{
    public function __construct(
        /**
         * ID des Datensatzes, an dem das Schlagwort hängt.
         *
         * Billomat-Feld: letter_id
         */
        public int $letterId,
        /**
         * Name des Schlagworts.
         *
         * Billomat-Feld: name
         */
        public string $name,
    ) {
    }

    /** Kopie mit geändertem `letterId`. */
    public function withLetterId(int $letterId): self
    {
        return clone($this, ['letterId' => $letterId]);
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
            'letter_id' => $this->letterId,
            'name' => $this->name,
        ];
    }
}
