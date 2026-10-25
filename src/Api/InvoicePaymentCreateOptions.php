<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use DateTimeImmutable;
use Justpilot\Billomat\Model\Enum\PaymentType;
use Justpilot\Billomat\Model\Money;

/**
 * Typisierter Payload für POST /invoice-payments gemäß Billomat-API.
 *
 * Null-Werte bedeuten "nicht gesetzt" und werden beim Serialisieren
 * (bis auf Pflichtfelder) entfernt, sodass Billomat Defaults verwenden kann.
 *
 * Doku:
 * https://www.billomat.com/api/rechnungen/zahlungen/
 *
 * Generiert aus tools/codegen/definitions/invoice-payments.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final readonly class InvoicePaymentCreateOptions implements WriteOptions
{
    public function __construct(
        /**
         * ID des Dokuments, zu dem die Zahlung gehört.
         *
         * Billomat-Feld: invoice_id
         * Typ: INT
         */
        public int $invoiceId,
        /**
         * Zahlungsbetrag.
         *
         * Billomat-Feld: amount
         * Typ: FLOAT
         */
        public Money $amount,
        /**
         * Zahlungsdatum.
         *
         * Format: YYYY-MM-DD
         *
         * Billomat-Feld: date
         * Typ: DATE
         * Default: heute
         */
        public ?DateTimeImmutable $date = null,
        /**
         * Zahlungsart.
         *
         * Billomat-Feld: type
         * Typ: ALNUM („CREDIT_NOTE“, „BANK_CARD“, „BANK_TRANSFER“, „DEBIT“, „CASH“, „CHECK“, „PAYPAL“, „CREDIT_CARD“, „COUPON“, „MISC“)
         */
        public ?PaymentType $type = null,
        /**
         * Kommentar zur Zahlung.
         *
         * Billomat-Feld: comment
         * Typ: ALNUM
         */
        public ?string $comment = null,
        /**
         * Verwendungszweck (z. B. Buchungstext aus dem Bankexport).
         *
         * Billomat-Feld: transaction_purpose
         */
        public ?string $transactionPurpose = null,
        /**
         * Ob das Dokument als bezahlt markiert werden soll.
         *
         * Billomat-Feld: mark_invoice_as_paid
         * Typ: BOOL
         * Default: 0 (false)
         */
        public bool $markInvoiceAsPaid = false,
    ) {
    }

    /** Kopie mit geändertem `invoiceId`. */
    public function withInvoiceId(int $invoiceId): self
    {
        return clone($this, ['invoiceId' => $invoiceId]);
    }

    /** Kopie mit geändertem `amount`. */
    public function withAmount(Money $amount): self
    {
        return clone($this, ['amount' => $amount]);
    }

    /** Kopie mit geändertem `date`. */
    public function withDate(?DateTimeImmutable $date): self
    {
        return clone($this, ['date' => $date]);
    }

    /** Kopie mit geändertem `type`. */
    public function withType(?PaymentType $type): self
    {
        return clone($this, ['type' => $type]);
    }

    /** Kopie mit geändertem `comment`. */
    public function withComment(?string $comment): self
    {
        return clone($this, ['comment' => $comment]);
    }

    /** Kopie mit geändertem `transactionPurpose`. */
    public function withTransactionPurpose(?string $transactionPurpose): self
    {
        return clone($this, ['transactionPurpose' => $transactionPurpose]);
    }

    /** Kopie mit geändertem `markInvoiceAsPaid`. */
    public function withMarkInvoiceAsPaid(bool $markInvoiceAsPaid): self
    {
        return clone($this, ['markInvoiceAsPaid' => $markInvoiceAsPaid]);
    }

    /**
     * Serialisiert die Options in ein Billomat-Payload-Array.
     *
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'invoice_id' => $this->invoiceId,
            'amount' => $this->amount->toString(),
            'date' => $this->date?->format('Y-m-d'),
            'comment' => $this->comment,
            'transaction_purpose' => $this->transactionPurpose,
            'type' => $this->type?->value,
            'mark_invoice_as_paid' => ($this->markInvoiceAsPaid ? 1 : 0),
        ], static fn (mixed $v): bool => null !== $v);
    }
}
