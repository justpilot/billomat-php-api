<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use DateTimeImmutable;
use Justpilot\Billomat\Internal\ScalarCaster;
use Justpilot\Billomat\Model\Enum\InvoicePaymentType;

use const DATE_ATOM;

/**
 * Repräsentiert eine Zahlung einer Rechnung in Billomat.
 *
 * Dokumentation:
 * https://www.billomat.com/en/api/invoices/payments/
 */
final readonly class InvoicePayment
{
    public function __construct(
        /** Interne ID der Zahlung */
        public ?int $id,
        /** ID der zugehörigen Rechnung */
        public int $invoiceId,
        /** Zahlungsdatum */
        public ?DateTimeImmutable $date,
        /** Zahlungsbetrag */
        public float $amount,
        /** Zahlungsart, z. B. BANK_TRANSFER, PAYPAL … */
        public ?InvoicePaymentType $type,
        /** Optionaler Kommentar */
        public ?string $comment,
        /** Erstellungszeitpunkt der Zahlung. */
        public ?DateTimeImmutable $created = null,
        /** ID des Users, der die Zahlung erfasst hat. */
        public ?int $userId = null,
        /** Verwendungszweck (z. B. Buchungstext aus dem Bankexport). */
        public ?string $transactionPurpose = null
    ) {
    }

    /**
     * Hydriert ein InvoicePayment-Objekt aus einem API-Array.
     *
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: ScalarCaster::toIntOrNull($data['id'] ?? null),
            invoiceId: (int) ($data['invoice_id'] ?? 0),
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
     * Exportiert die Zahlung zurück in ein Billomat-kompatibles Array.
     *
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'created' => $this->created?->format(DATE_ATOM),
            'invoice_id' => $this->invoiceId,
            'user_id' => $this->userId,
            'date' => $this->date?->format('Y-m-d'),
            'amount' => $this->amount,
            'type' => $this->type?->value,
            'comment' => $this->comment,
            'transaction_purpose' => $this->transactionPurpose,
        ];
    }
}
