<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use DateTimeImmutable;
use Justpilot\Billomat\Internal\ScalarCaster;
use Justpilot\Billomat\Model\Enum\PaymentType;

use const DATE_ATOM;

/**
 * Zahlung einer Eingangsrechnung.
 *
 * Dieses Model bildet die Struktur von GET /incoming-payments?incoming_id={id} bzw. GET /incoming-payments/{id} ab.
 *
 * Dokumentation:
 * https://www.billomat.com/api/eingangsrechnungen/zahlungen/
 *
 * Generiert aus tools/codegen/definitions/incoming-payments.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final readonly class IncomingPayment
{
    /** Billomat-Felder, die das Model abbildet. */
    private const array FIELDS = [
        'id' => true,
        'incoming_id' => true,
        'date' => true,
        'amount' => true,
        'type' => true,
        'comment' => true,
        'created' => true,
        'user_id' => true,
        'transaction_purpose' => true,
        'customfield' => true,
        'currency_code' => true,
        'quote' => true,
        'payment_group' => true,
        'finapi_transaction_id' => true,
        'finapi_transaction_amount' => true,
        'document_title' => true,
    ];

    /** @param array<string, mixed> $extra */
    public function __construct(
        /** Interne ID der Zahlung. */
        public ?int $id,
        /** ID des Dokuments, zu dem die Zahlung gehört. */
        public int $incomingId,
        /** Zahlungsdatum. */
        public ?DateTimeImmutable $date,
        /** Zahlungsbetrag. */
        public Money $amount,
        /** Zahlungsart. */
        public ?PaymentType $type,
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
        public ?Money $finapiTransactionAmount = null,
        /** Titel des zugehörigen Dokuments. */
        public ?string $documentTitle = null,
        /**
         * Felder, die Billomat liefert, das SDK aber (noch) nicht abbildet – roh, wie in der Response.
         *
         * Neue Billomat-Felder gehen so nicht verloren; toArray() gibt sie mit aus.
         */
        public array $extra = [],
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
            incomingId: ScalarCaster::toIntOrNull($data['incoming_id'] ?? null) ?? 0,
            date: ScalarCaster::toDateTimeOrNull($data['date'] ?? null),
            amount: ScalarCaster::toMoneyOrNull($data['amount'] ?? null) ?? Money::zero(),
            type: PaymentType::fromApi(ScalarCaster::toStringOrNull($data['type'] ?? null)),
            comment: ScalarCaster::toStringOrNull($data['comment'] ?? null),
            created: ScalarCaster::toDateTimeOrNull($data['created'] ?? null),
            userId: ScalarCaster::toIntOrNull($data['user_id'] ?? null),
            transactionPurpose: ScalarCaster::toStringOrNull($data['transaction_purpose'] ?? null),
            customfield: ScalarCaster::toStringOrNull($data['customfield'] ?? null),
            currencyCode: ScalarCaster::toStringOrNull($data['currency_code'] ?? null),
            quote: ScalarCaster::toFloatOrNull($data['quote'] ?? null),
            paymentGroup: ScalarCaster::toStringOrNull($data['payment_group'] ?? null),
            finapiTransactionId: ScalarCaster::toStringOrNull($data['finapi_transaction_id'] ?? null),
            finapiTransactionAmount: ScalarCaster::toMoneyOrNull($data['finapi_transaction_amount'] ?? null),
            documentTitle: ScalarCaster::toStringOrNull($data['document_title'] ?? null),
            extra: array_diff_key($data, self::FIELDS),
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
            ...$this->extra,
            'id' => $this->id,
            'created' => $this->created?->format(DATE_ATOM),
            'incoming_id' => $this->incomingId,
            'user_id' => $this->userId,
            'date' => $this->date?->format('Y-m-d'),
            'amount' => $this->amount->toString(),
            'type' => $this->type?->value,
            'comment' => $this->comment,
            'transaction_purpose' => $this->transactionPurpose,
            'customfield' => $this->customfield,
            'currency_code' => $this->currencyCode,
            'quote' => $this->quote,
            'payment_group' => $this->paymentGroup,
            'finapi_transaction_id' => $this->finapiTransactionId,
            'finapi_transaction_amount' => $this->finapiTransactionAmount?->toString(),
            'document_title' => $this->documentTitle,
        ];
    }
}
