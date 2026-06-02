<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use DateTimeImmutable;
use Justpilot\Billomat\Internal\ScalarCaster;
use Justpilot\Billomat\Model\Enum\InvoicePaymentType;

use const DATE_ATOM;

/**
 * Zahlung/Auszahlung einer Gutschrift.
 *
 * Doku: https://www.billomat.com/en/api/credit-notes/payments/
 */
final readonly class CreditNotePayment
{
    public function __construct(
        public ?int $id,
        public int $creditNoteId,
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
            id: ScalarCaster::toIntOrNull($data['id'] ?? null),
            creditNoteId: (int) ($data['credit_note_id'] ?? 0),
            date: ScalarCaster::toDateTimeOrNull($data['date'] ?? null),
            amount: isset($data['amount']) ? (float) $data['amount'] : 0.0,
            type: InvoicePaymentType::fromApi($data['type'] ?? null),
            comment: ScalarCaster::toStringOrNull($data['comment'] ?? null),
            created: ScalarCaster::toDateTimeOrNull($data['created'] ?? null),
            userId: ScalarCaster::toIntOrNull($data['user_id'] ?? null),
            transactionPurpose: ScalarCaster::toStringOrNull($data['transaction_purpose'] ?? null),
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
            'credit_note_id' => $this->creditNoteId,
            'user_id' => $this->userId,
            'date' => $this->date?->format('Y-m-d'),
            'amount' => $this->amount,
            'type' => $this->type?->value,
            'comment' => $this->comment,
            'transaction_purpose' => $this->transactionPurpose,
        ];
    }
}
