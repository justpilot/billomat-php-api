<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\Enum\RecurringEmailReceiverType;

/**
 * Payload für POST /recurring-email-receivers.
 *
 * Doku: https://www.billomat.com/api/abo-rechnungen/email-empfaenger/
 */
final class RecurringEmailReceiverCreateOptions
{
    public function __construct(
        public int $recurringId,
        public RecurringEmailReceiverType $type,
        public string $address,
    ) {
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'recurring_id' => $this->recurringId,
            'type' => $this->type->value,
            'address' => $this->address,
        ];
    }
}
