<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

/**
 * Payload für POST /client-tags.
 */
final class ClientTagCreateOptions
{
    public function __construct(
        public int $clientId,
        public string $name,
    ) {
    }

    /**
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
