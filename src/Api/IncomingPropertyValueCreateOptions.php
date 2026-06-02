<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

/**
 * Payload für POST /incoming-property-values.
 */
final class IncomingPropertyValueCreateOptions
{
    public function __construct(
        public int $incomingId,
        public int $incomingPropertyId,
        public mixed $value,
    ) {
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'incoming_id' => $this->incomingId,
            'incoming_property_id' => $this->incomingPropertyId,
            'value' => $this->value,
        ];
    }
}
