<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use DateTimeImmutable;
use Justpilot\Billomat\Model\Enum\InvoicePaymentType;

use const ARRAY_FILTER_USE_BOTH;

/**
 * Typisierter Payload für POST /credit-note-payments.
 *
 * Doku: https://www.billomat.com/en/api/credit-notes/payments/
 */
final class CreditNotePaymentCreateOptions
{
    public ?DateTimeImmutable $date = null;

    public ?string $comment = null;

    public ?string $transactionPurpose = null;

    public ?InvoicePaymentType $type = null;

    /** Ob die Gutschrift als bezahlt markiert werden soll. */
    public bool $markCreditNoteAsPaid = false;

    public function __construct(
        public int $creditNoteId,
        public float $amount,
    ) {
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        $data = [
            'credit_note_id' => $this->creditNoteId,
            'amount' => $this->amount,
            'date' => $this->date?->format('Y-m-d'),
            'comment' => $this->comment,
            'transaction_purpose' => $this->transactionPurpose,
            'type' => $this->type?->value,
            'mark_credit_note_as_paid' => $this->markCreditNoteAsPaid ? 1 : 0,
        ];

        return array_filter(
            $data,
            static fn (mixed $v, string $k): bool => null !== $v || \in_array($k, ['credit_note_id', 'amount', 'mark_credit_note_as_paid'], true),
            ARRAY_FILTER_USE_BOTH,
        );
    }
}
