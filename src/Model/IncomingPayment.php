<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use DateTimeImmutable;
use Justpilot\Billomat\Model\Enum\InvoicePaymentType;
use Throwable;

use const DATE_ATOM;

/**
 * Zahlung einer Eingangsrechnung.
 *
 * Doku: https://www.billomat.com/en/api/incomings/payments/
 */
final readonly class IncomingPayment
{
    public function __construct(
        public ?int $id,
        public int $incomingId,
        public ?DateTimeImmutable $date,
        public float $amount,
        public ?InvoicePaymentType $type,
        public ?string $comment,
        public ?DateTimeImmutable $created = null,
        public ?int $userId = null,
        public ?string $transactionPurpose = null,
    ) {
    }

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            incomingId: (int) ($data['incoming_id'] ?? 0),
            date: self::parseDateTime($data['date'] ?? null),
            amount: isset($data['amount']) ? (float) $data['amount'] : 0.0,
            type: InvoicePaymentType::fromApi($data['type'] ?? null),
            comment: $data['comment'] ?? null,
            created: self::parseDateTime($data['created'] ?? null),
            userId: isset($data['user_id']) && '' !== $data['user_id']
                ? (int) $data['user_id']
                : null,
            transactionPurpose: $data['transaction_purpose'] ?? null,
        );
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'created' => $this->created?->format(DATE_ATOM),
            'incoming_id' => $this->incomingId,
            'user_id' => $this->userId,
            'date' => $this->date?->format('Y-m-d'),
            'amount' => $this->amount,
            'type' => $this->type?->value,
            'comment' => $this->comment,
            'transaction_purpose' => $this->transactionPurpose,
        ];
    }

    private static function parseDateTime(mixed $value): ?DateTimeImmutable
    {
        if (!\is_string($value) || '' === trim($value)) {
            return null;
        }

        try {
            return new DateTimeImmutable($value);
        } catch (Throwable) {
            return null;
        }
    }
}
