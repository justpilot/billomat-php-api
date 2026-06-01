<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use Justpilot\Billomat\Model\Enum\InvoicePaymentType;

/**
 * Repräsentiert eine Zahlung einer Rechnung in Billomat.
 *
 * Dokumentation:
 * https://www.billomat.com/en/api/invoices/payments/
 */
final readonly class InvoicePayment
{
    /** Interne ID der Zahlung */
    public ?int $id;

    /** Erstellungszeitpunkt der Zahlung. */
    public ?\DateTimeImmutable $created;

    /** ID der zugehörigen Rechnung */
    public int $invoiceId;

    /** ID des Users, der die Zahlung erfasst hat. */
    public ?int $userId;

    /** Zahlungsdatum */
    public ?\DateTimeImmutable $date;

    /** Zahlungsbetrag */
    public float $amount;

    /** Zahlungsart, z. B. BANK_TRANSFER, PAYPAL … */
    public ?InvoicePaymentType $type;

    /** Optionaler Kommentar */
    public ?string $comment;

    /** Verwendungszweck (z. B. Buchungstext aus dem Bankexport). */
    public ?string $transactionPurpose;

    public function __construct(
        ?int                $id,
        int                 $invoiceId,
        ?\DateTimeImmutable $date,
        float               $amount,
        ?InvoicePaymentType $type,
        ?string             $comment,
        ?\DateTimeImmutable $created = null,
        ?int                $userId = null,
        ?string             $transactionPurpose = null,
    )
    {
        $this->id = $id;
        $this->invoiceId = $invoiceId;
        $this->date = $date;
        $this->amount = $amount;
        $this->type = $type;
        $this->comment = $comment;
        $this->created = $created;
        $this->userId = $userId;
        $this->transactionPurpose = $transactionPurpose;
    }

    /**
     * Hydriert ein InvoicePayment-Objekt aus einem API-Array.
     *
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int)$data['id'] : null,
            invoiceId: (int)($data['invoice_id'] ?? 0),
            date: self::parseDateTime($data['date'] ?? null),
            amount: isset($data['amount']) ? (float)$data['amount'] : 0.0,
            type: InvoicePaymentType::fromApi($data['type'] ?? null),
            comment: $data['comment'] ?? null,
            created: self::parseDateTime($data['created'] ?? null),
            userId: isset($data['user_id']) && $data['user_id'] !== ''
                ? (int)$data['user_id']
                : null,
            transactionPurpose: $data['transaction_purpose'] ?? null,
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
            'created' => $this->created?->format(\DATE_ATOM),
            'invoice_id' => $this->invoiceId,
            'user_id' => $this->userId,
            'date' => $this->date?->format('Y-m-d'),
            'amount' => $this->amount,
            'type' => $this->type?->value,
            'comment' => $this->comment,
            'transaction_purpose' => $this->transactionPurpose,
        ];
    }

    private static function parseDateTime(mixed $value): ?\DateTimeImmutable
    {
        if (!is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            return new \DateTimeImmutable($value);
        } catch (\Throwable) {
            return null;
        }
    }
}
