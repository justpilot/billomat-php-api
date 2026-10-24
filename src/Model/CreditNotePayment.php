<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use DateTimeImmutable;
use Justpilot\Billomat\Internal\ScalarCaster;
use Justpilot\Billomat\Model\Enum\InvoicePaymentType;

use const DATE_ATOM;

/**
 * Zahlung einer Gutschrift.
 *
 * Dieses Model bildet die Struktur von GET /credit-note-payments?credit_note_id={id} bzw. GET /credit-note-payments/{id} ab.
 *
 * Dokumentation:
 * https://www.billomat.com/api/gutschriften/zahlungen/
 *
 * Generiert aus tools/codegen/definitions/credit-note-payments.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final readonly class CreditNotePayment
{
    public function __construct(
        /** Interne ID der Zahlung. */
        public ?int $id,
        /** ID des Dokuments, zu dem die Zahlung gehört. */
        public int $creditNoteId,
        /** Zahlungsdatum. */
        public ?DateTimeImmutable $date,
        /** Zahlungsbetrag. */
        public float $amount,
        /** Zahlungsart. */
        public ?InvoicePaymentType $type,
        /** Kommentar zur Zahlung. */
        public ?string $comment,
        /** Erstellungszeitpunkt der Zahlung. */
        public ?DateTimeImmutable $created = null,
        /** ID des Users, der die Zahlung erfasst hat. */
        public ?int $userId = null,
        /** Verwendungszweck (z. B. Buchungstext aus dem Bankexport). */
        public ?string $transactionPurpose = null,
        /** Freies Zusatzfeld (siehe Custom-Meta-Attribute). */
        public ?string $customfield = null,
        /** Währung der Zahlung (ISO-Währungscode). */
        public ?string $currencyCode = null,
        /** Umrechnungskurs zur Hauptwährung. */
        public ?float $quote = null,
        /** Gruppe zusammengehöriger Zahlungen (z. B. Sammelzahlung). */
        public ?string $paymentGroup = null,
        /** ID der Banktransaktion (finAPI), falls die Zahlung daraus stammt. */
        public ?string $finapiTransactionId = null,
        /** Betrag der Banktransaktion (finAPI). */
        public ?float $finapiTransactionAmount = null,
        /** Titel des zugehörigen Dokuments. */
        public ?string $documentTitle = null,
    ) {
    }

    /**
     * Hydriert das Model aus einem Billomat-Response-Array.
     *
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: ScalarCaster::toIntOrNull($data['id'] ?? null),
            creditNoteId: ScalarCaster::toIntOrNull($data['credit_note_id'] ?? null) ?? 0,
            date: ScalarCaster::toDateTimeOrNull($data['date'] ?? null),
            amount: ScalarCaster::toFloatOrNull($data['amount'] ?? null) ?? 0.0,
            type: InvoicePaymentType::tryFrom(ScalarCaster::toStringOrNull($data['type'] ?? null) ?? ''),
            comment: ScalarCaster::toStringOrNull($data['comment'] ?? null),
            created: ScalarCaster::toDateTimeOrNull($data['created'] ?? null),
            userId: ScalarCaster::toIntOrNull($data['user_id'] ?? null),
            transactionPurpose: ScalarCaster::toStringOrNull($data['transaction_purpose'] ?? null),
            customfield: ScalarCaster::toStringOrNull($data['customfield'] ?? null),
            currencyCode: ScalarCaster::toStringOrNull($data['currency_code'] ?? null),
            quote: ScalarCaster::toFloatOrNull($data['quote'] ?? null),
            paymentGroup: ScalarCaster::toStringOrNull($data['payment_group'] ?? null),
            finapiTransactionId: ScalarCaster::toStringOrNull($data['finapi_transaction_id'] ?? null),
            finapiTransactionAmount: ScalarCaster::toFloatOrNull($data['finapi_transaction_amount'] ?? null),
            documentTitle: ScalarCaster::toStringOrNull($data['document_title'] ?? null),
        );
    }

    /**
     * Exportiert das Model als Array mit Billomat-Feldnamen.
     *
     * Praktisch für Debugging oder Log-Ausgaben.
     *
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'created' => $this->created?->format(DATE_ATOM),
            'credit_note_id' => $this->creditNoteId,
            'user_id' => $this->userId,
            'date' => $this->date?->format('Y-m-d'),
            'amount' => $this->amount,
            'type' => $this->type?->value,
            'comment' => $this->comment,
            'transaction_purpose' => $this->transactionPurpose,
            'customfield' => $this->customfield,
            'currency_code' => $this->currencyCode,
            'quote' => $this->quote,
            'payment_group' => $this->paymentGroup,
            'finapi_transaction_id' => $this->finapiTransactionId,
            'finapi_transaction_amount' => $this->finapiTransactionAmount,
            'document_title' => $this->documentTitle,
        ];
    }
}
