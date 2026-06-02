<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use DateTimeImmutable;
use Justpilot\Billomat\Model\Enum\InvoicePaymentType;

use const ARRAY_FILTER_USE_BOTH;

/**
 * Typisierter Payload für POST /incoming-payments.
 *
 * Doku: https://www.billomat.com/en/api/incomings/payments/
 */
final class IncomingPaymentCreateOptions
{
    public ?DateTimeImmutable $date = null;

    public ?string $comment = null;

    public ?string $transactionPurpose = null;

    public ?InvoicePaymentType $type = null;

    /** Ob die Eingangsrechnung als bezahlt markiert werden soll. */
    public bool $markIncomingAsPaid = false;

    public function __construct(
        public int $incomingId,
        public float $amount,
    ) {
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        $data = [
            'incoming_id' => $this->incomingId,
            'amount' => $this->amount,
            'date' => $this->date?->format('Y-m-d'),
            'comment' => $this->comment,
            'transaction_purpose' => $this->transactionPurpose,
            'type' => $this->type?->value,
            'mark_incoming_as_paid' => $this->markIncomingAsPaid ? 1 : 0,
        ];

        return array_filter(
            $data,
            static fn (mixed $v, string $k): bool => null !== $v || \in_array($k, ['incoming_id', 'amount', 'mark_incoming_as_paid'], true),
            ARRAY_FILTER_USE_BOTH,
        );
    }
}
