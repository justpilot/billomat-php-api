<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use DateTimeImmutable;
use Justpilot\Billomat\Model\Enum\InvoicePaymentType;

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
final class CreditNotePaymentCreateOptions
{
    public function __construct(
        /**
         * ID des Dokuments, zu dem die Zahlung gehört.
         *
         * Billomat-Feld: credit_note_id
         * Typ: INT
         */
        public int $creditNoteId,
        /**
         * Zahlungsbetrag.
         *
         * Billomat-Feld: amount
         * Typ: FLOAT
         */
        public float $amount,
    ) {
    }

    /**
     * Zahlungsdatum.
     *
     * Format: YYYY-MM-DD
     *
     * Billomat-Feld: date
     * Typ: DATE
     * Default: heute
     */
    public ?DateTimeImmutable $date = null;

    /**
     * Zahlungsart.
     *
     * Billomat-Feld: type
     * Typ: ALNUM („CREDIT_NOTE“, „BANK_TRANSFER“, „DEBIT“, „CASH“, „PAYPAL“, „CREDIT_CARD“, „MISC“)
     */
    public ?InvoicePaymentType $type = null;

    /**
     * Kommentar zur Zahlung.
     *
     * Billomat-Feld: comment
     * Typ: ALNUM
     */
    public ?string $comment = null;

    /**
     * Verwendungszweck (z. B. Buchungstext aus dem Bankexport).
     *
     * Billomat-Feld: transaction_purpose
     */
    public ?string $transactionPurpose = null;

    /**
     * Ob das Dokument als bezahlt markiert werden soll.
     *
     * Billomat-Feld: mark_credit_note_as_paid
     * Typ: BOOL
     * Default: 0 (false)
     */
    public bool $markCreditNoteAsPaid = false;

    /**
     * Serialisiert die Options in ein Billomat-Payload-Array.
     *
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
            'mark_credit_note_as_paid' => ($this->markCreditNoteAsPaid ? 1 : 0),
        ];

        // Null-Felder entfernen
        return array_filter($data, static fn (mixed $v): bool => null !== $v);
    }
}
