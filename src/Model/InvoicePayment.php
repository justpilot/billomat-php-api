<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use Justpilot\Billomat\Model\Enum\InvoicePaymentType;

/**
 * Repräsentiert eine Zahlung einer Rechnung in Billomat.
 *
 * Dokumentation:
 * https://www.billomat.com/api/rechnungen/zahlungen/
 */
final readonly class InvoicePayment
{
    /** Interne ID der Zahlung */
    public ?int $id;

    /** ID der zugehörigen Rechnung */
    public int $invoiceId;

    /** Zahlungsdatum */
    public ?\DateTimeImmutable $date;

    /** Zahlungsbetrag */
    public float $amount;

    /** Zahlungsart, z. B. BANK_TRANSFER, PAYPAL … */
    public ?InvoicePaymentType $type;

    /** Optionaler Kommentar */
    public ?string $comment;

    public function __construct(
        ?int                $id,
        int                 $invoiceId,
        ?\DateTimeImmutable $date,
        float               $amount,
        ?InvoicePaymentType $type,
        ?string             $comment,
    )
    {
        $this->id = $id;
        $this->invoiceId = $invoiceId;
        $this->date = $date;
        $this->amount = $amount;
        $this->type = $type;
        $this->comment = $comment;
    }

    /**
     * Hydriert ein InvoicePayment-Objekt aus einem API-Array.
     *
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        // Datum parsen
        $date = null;
        if (!empty($data['date'])) {
            try {
                $date = new \DateTimeImmutable((string)$data['date']);
            } catch (\Throwable) {
                $date = null;
            }
        }

        return new self(
            id: isset($data['id']) ? (int)$data['id'] : null,
            invoiceId: (int)($data['invoice_id'] ?? 0),
            date: $date,
            amount: isset($data['amount']) ? (float)$data['amount'] : 0.0,
            type: InvoicePaymentType::fromApi($data['type'] ?? null),
            comment: $data['comment'] ?? null,
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
            'invoice_id' => $this->invoiceId,
            'date' => $this->date?->format('Y-m-d'),
            'amount' => $this->amount,
            'type' => $this->type?->value,
            'comment' => $this->comment,
        ];
    }
}