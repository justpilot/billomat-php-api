<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use DateTimeImmutable;
use Justpilot\Billomat\Model\Enum\InvoicePaymentType;

/**
 * Typisierter Payload für POST /incoming-payments gemäß Billomat-API.
 *
 * Null-Werte bedeuten "nicht gesetzt" und werden beim Serialisieren
 * (bis auf Pflichtfelder) entfernt, sodass Billomat Defaults verwenden kann.
 *
 * Doku:
 * https://www.billomat.com/api/eingangsrechnungen/zahlungen/
 *
 * Generiert aus tools/codegen/definitions/incoming-payments.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final class IncomingPaymentCreateOptions implements WriteOptions
{
    public function __construct(
        /**
         * ID des Dokuments, zu dem die Zahlung gehört.
         *
         * Billomat-Feld: incoming_id
         * Typ: INT
         */
        public int $incomingId,
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
     * Typ: ALNUM („CREDIT_NOTE“, „BANK_CARD“, „BANK_TRANSFER“, „DEBIT“, „CASH“, „CHECK“, „PAYPAL“, „CREDIT_CARD“, „COUPON“, „MISC“)
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
     * Billomat-Feld: mark_incoming_as_paid
     * Typ: BOOL
     * Default: 0 (false)
     */
    public bool $markIncomingAsPaid = false;

    /**
     * Serialisiert die Options in ein Billomat-Payload-Array.
     *
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
            'mark_incoming_as_paid' => ($this->markIncomingAsPaid ? 1 : 0),
        ];

        // Null-Felder entfernen
        return array_filter($data, static fn (mixed $v): bool => null !== $v);
    }
}
