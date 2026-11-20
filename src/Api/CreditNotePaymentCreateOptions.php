<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use DateTimeImmutable;
use Justpilot\Billomat\Model\Enum\PaymentType;
use Justpilot\Billomat\Model\Money;

/**
 * Typisierter Payload für POST /credit-note-payments gemäß Billomat-API.
 *
 * Null-Werte bedeuten "nicht gesetzt" und werden beim Serialisieren
 * (bis auf Pflichtfelder) entfernt, sodass Billomat Defaults verwenden kann.
 *
 * Doku:
 * https://www.billomat.com/api/gutschriften/zahlungen/
 *
 * Generiert aus tools/codegen/definitions/credit-note-payments.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final readonly class CreditNotePaymentCreateOptions implements WriteOptions
{
    public function __construct(
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
         * Typ: ALNUM („CREDIT_NOTE“, „BANK_TRANSFER“, „DEBIT“, „CASH“, „PAYPAL“, „CREDIT_CARD“, „MISC“)
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
         * Billomat-Feld: mark_credit_note_as_paid
         * Typ: BOOL
         * Default: 0 (false)
         */
        public bool $markCreditNoteAsPaid = false,
    ) {
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

    /** Kopie mit geändertem `markCreditNoteAsPaid`. */
    public function withMarkCreditNoteAsPaid(bool $markCreditNoteAsPaid): self
    {
        return clone($this, ['markCreditNoteAsPaid' => $markCreditNoteAsPaid]);
    }

    /**
     * Serialisiert die Options in ein Billomat-Payload-Array.
     *
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'amount' => $this->amount->toString(),
            'date' => $this->date?->format('Y-m-d'),
            'comment' => $this->comment,
            'transaction_purpose' => $this->transactionPurpose,
            'type' => $this->type?->value,
            'mark_credit_note_as_paid' => ($this->markCreditNoteAsPaid ? 1 : 0),
        ], static fn (mixed $v): bool => null !== $v);
    }
}
