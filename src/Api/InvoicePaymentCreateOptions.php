<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\Enum\InvoicePaymentType;

/**
 * Typisierter Payload für POST /invoice-payments.
 *
 * Dokumentation:
 * https://www.billomat.com/api/rechnungen/zahlungen/
 *
 * Null-Werte bedeuten "nicht gesetzt" und werden beim Serialisieren
 * (bis auf Pflichtfelder) entfernt, sodass Billomat Defaults verwenden kann.
 */
final class InvoicePaymentCreateOptions
{
    /**
     * ID der Rechnung.
     *
     * Billomat-Feld: invoice_id
     * Typ: INT
     * Pflicht: ja
     */
    public int $invoiceId;

    /**
     * Gezahlter Betrag.
     *
     * Billomat-Feld: amount
     * Typ: FLOAT
     * Pflicht: ja
     */
    public float $amount;

    /**
     * Zahlungsdatum (YYYY-MM-DD).
     *
     * Billomat-Feld: date
     * Typ: DATE
     * Default: heute
     */
    public ?\DateTimeImmutable $date = null;

    /**
     * Kommentar zur Zahlung.
     *
     * Billomat-Feld: comment
     * Typ: ALNUM
     */
    public ?string $comment = null;

    /**
     * Verwendungszweck / Transaction Purpose.
     *
     * Billomat-Feld: transaction_purpose
     * Typ: ALNUM
     */
    public ?string $transactionPurpose = null;

    /**
     * Zahlungsart.
     *
     * Billomat-Feld: type
     * Typ: ENUM
     * Mögliche Werte: INVOICE_CORRECTION, CREDIT_NOTE, BANK_CARD, ...
     */
    public ?InvoicePaymentType $type = null;

    /**
     * Ob die Rechnung als bezahlt markiert werden soll.
     *
     * Billomat-Feld: mark_invoice_as_paid
     * Typ: BOOL (0/1)
     * Default: false (0)
     */
    public bool $markInvoiceAsPaid = false;

    public function __construct(int $invoiceId, float $amount)
    {
        $this->invoiceId = $invoiceId;
        $this->amount = $amount;
    }

    /**
     * Serialisiert in ein Billomat-kompatibles Array.
     *
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        $data = [
            'invoice_id' => $this->invoiceId,
            'amount' => $this->amount,
            'date' => $this->date?->format('Y-m-d'),
            'comment' => $this->comment,
            'transaction_purpose' => $this->transactionPurpose,
            'type' => $this->type?->value,
            'mark_invoice_as_paid' => $this->markInvoiceAsPaid ? 1 : 0,
        ];

        // invoice_id, amount & mark_invoice_as_paid dürfen nicht weggefiltert werden
        return array_filter(
            $data,
            static fn(mixed $v, string $k): bool => $v !== null || \in_array($k, ['invoice_id', 'amount', 'mark_invoice_as_paid'], true),
            \ARRAY_FILTER_USE_BOTH
        );
    }
}