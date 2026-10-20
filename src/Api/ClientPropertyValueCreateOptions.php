<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

/**
 * Payload für POST /client-property-values.
 */
final class ClientPropertyValueCreateOptions
{
    public function __construct(
        public int $clientId,
        public int $clientPropertyId,
        public mixed $value,
    ) {
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'client_id' => $this->clientId,
            'client_property_id' => $this->clientPropertyId,
            'value' => $this->value,
        ];
    }
}
