<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

/**
 * Typisierter Payload für POST /client-tags gemäß Billomat-API.
 *
 * Doku:
 * https://www.billomat.com/api/kunden/schlagworte/
 *
 * Generiert aus tools/codegen/definitions/client-tags.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final readonly class ClientTagCreateOptions implements WriteOptions
{
    public function __construct(
        /**
         * ID des Datensatzes, an dem das Schlagwort hängt.
         *
         * Billomat-Feld: client_id
         * Typ: INT
         */
        public int $clientId,
        /**
         * Name des Schlagworts.
         *
         * Billomat-Feld: name
         * Typ: ALNUM
         */
        public string $name,
    ) {
    }

    /** Kopie mit geändertem `clientId`. */
    public function withClientId(int $clientId): self
    {
        return clone($this, ['clientId' => $clientId]);
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
            'client_id' => $this->clientId,
            'name' => $this->name,
        ];
    }
}
