<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use Justpilot\Billomat\Internal\ScalarCaster;
use Justpilot\Billomat\Model\Enum\RecurringEmailReceiverType;

/**
 * E-Mail-Empfänger für eine Abo-Rechnung (TO/CC/BCC).
 *
 * Doku: https://www.billomat.com/api/abo-rechnungen/email-empfaenger/
 */
final readonly class RecurringEmailReceiver
{
    public function __construct(
        public ?int $id,
        public int $recurringId,
        public RecurringEmailReceiverType $type,
        public string $address,
    ) {
    }

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: ScalarCaster::toIntOrNull($data['id'] ?? null),
            recurringId: (int) ($data['recurring_id'] ?? 0),
            type: RecurringEmailReceiverType::from((string) ($data['type'] ?? 'to')),
            address: (string) ($data['address'] ?? ''),
        );
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'recurring_id' => $this->recurringId,
            'type' => $this->type->value,
            'address' => $this->address,
        ];
    }
}
