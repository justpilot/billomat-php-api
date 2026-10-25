<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use DateTimeImmutable;
use Justpilot\Billomat\Internal\ListCaster;
use Justpilot\Billomat\Internal\ScalarCaster;
use Justpilot\Billomat\Model\Enum\ConfirmationStatus;
use Justpilot\Billomat\Model\Enum\NetGross;

use const DATE_ATOM;

/**
 * Repräsentiert eine Auftragsbestätigung (Confirmation) aus der Billomat-API.
 *
 * Dokumentation:
 * https://www.billomat.com/en/api/confirmations/
 *
 * Generiert aus tools/codegen/definitions/confirmations.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final readonly class Confirmation
{
    /** Billomat-Felder, die das Model abbildet. */
    private const array FIELDS = [
        'id' => true,
        'client_id' => true,
        'contact_id' => true,
        'created' => true,
        'confirmation_number' => true,
        'number' => true,
        'number_pre' => true,
        'number_length' => true,
        'status' => true,
        'date' => true,
        'address' => true,
        'title' => true,
        'label' => true,
        'intro' => true,
        'note' => true,
        'total_gross' => true,
        'total_net' => true,
        'net_gross' => true,
        'reduction' => true,
        'total_gross_unreduced' => true,
        'total_net_unreduced' => true,
        'currency_code' => true,
        'quote' => true,
        'offer_id' => true,
        'free_text_id' => true,
        'template_id' => true,
        'taxes' => true,
        'confirmation-items' => true,
        'updated' => true,
        'customfield' => true,
        'total_reduction' => true,
    ];

    /**
     * @param list<TaxSummary>       $taxes
     * @param list<ConfirmationItem> $items
     * @param array<string, mixed>   $extra
     */
    public function __construct(
        /** Interne Billomat-ID der Auftragsbestätigung. */
        public ?int $id,
        /** ID des Kunden. */
        public int $clientId,
        /** ID des Kontakts. */
        public ?int $contactId = null,
        /** Erstellungszeitpunkt der Auftragsbestätigung. */
        public ?DateTimeImmutable $created = null,
        /** Auftragsbestätigungsnummer (wird beim Abschließen vergeben). */
        public ?string $confirmationNumber = null,
        /** Laufende Nummer (ohne Präfix). */
        public ?int $number = null,
        /** Präfix der Auftragsbestätigungsnummer. */
        public ?string $numberPre = null,
        /** Mindestlänge der Auftragsbestätigungsnummer (wird mit führenden Nullen aufgefüllt). */
        public ?int $numberLength = null,
        /** Status der Auftragsbestätigung. */
        public ?ConfirmationStatus $status = null,
        /** Auftragsbestätigungsdatum. */
        public ?DateTimeImmutable $date = null,
        /**
         * Komplette Adresse.
         *
         * Laut Doku veraltet – Billomat verarbeitet das Feld nicht mehr, bitte contact_id oder client_id verwenden.
         */
        public ?string $address = null,
        /** Dokumentenüberschrift. */
        public ?string $title = null,
        /** Bezeichnung. */
        public ?string $label = null,
        /** Einleitungstext. */
        public ?string $intro = null,
        /** Anmerkungstext. */
        public ?string $note = null,
        /** Gesamtbetrag brutto. */
        public ?Money $totalGross = null,
        /** Gesamtbetrag netto. */
        public ?Money $totalNet = null,
        /** Preisbasis (Brutto- oder Netto-Preise). */
        public ?NetGross $netGross = null,
        /** Rabatt (absolut oder als Prozentwert, z. B. "10" oder "10%"). */
        public ?string $reduction = null,
        /** Gesamtbetrag brutto vor Rabatt. */
        public ?Money $totalGrossUnreduced = null,
        /** Gesamtbetrag netto vor Rabatt. */
        public ?Money $totalNetUnreduced = null,
        /** Währung (ISO-Währungscode). */
        public ?string $currencyCode = null,
        /** Währungskurs (für Umrechnung in die Standard-Währung). */
        public ?float $quote = null,
        /** ID des Quell-Angebots. */
        public ?int $offerId = null,
        /** ID des Freitextes zur Belegung von title, label, intro und note. */
        public ?int $freeTextId = null,
        /** ID der Vorlage, mit der die Auftragsbestätigung abgeschlossen wird. */
        public ?int $templateId = null,
        /** Steuerzeilen der Auftragsbestätigung. */
        public array $taxes = [],
        /** Positionen der Auftragsbestätigung (nur bei GET /confirmations/{id}). */
        public array $items = [],
        /** Zeitpunkt der letzten Änderung. */
        public ?DateTimeImmutable $updated = null,
        /** Freies Zusatzfeld (siehe Custom-Meta-Attribute). */
        public ?string $customfield = null,
        /** Summe der Rabatte. */
        public ?Money $totalReduction = null,
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
            clientId: ScalarCaster::toIntOrNull($data['client_id'] ?? null) ?? 0,
            contactId: ScalarCaster::toIntOrNull($data['contact_id'] ?? null),
            created: ScalarCaster::toDateTimeOrNull($data['created'] ?? null),
            confirmationNumber: ScalarCaster::toStringOrNull($data['confirmation_number'] ?? null),
            number: ScalarCaster::toIntOrNull($data['number'] ?? null),
            numberPre: ScalarCaster::toStringOrNull($data['number_pre'] ?? null),
            numberLength: ScalarCaster::toIntOrNull($data['number_length'] ?? null),
            status: ConfirmationStatus::tryFrom(ScalarCaster::toStringOrNull($data['status'] ?? null) ?? ''),
            date: ScalarCaster::toDateTimeOrNull($data['date'] ?? null),
            address: ScalarCaster::toStringOrNull($data['address'] ?? null),
            title: ScalarCaster::toStringOrNull($data['title'] ?? null),
            label: ScalarCaster::toStringOrNull($data['label'] ?? null),
            intro: ScalarCaster::toStringOrNull($data['intro'] ?? null),
            note: ScalarCaster::toStringOrNull($data['note'] ?? null),
            totalGross: ScalarCaster::toMoneyOrNull($data['total_gross'] ?? null),
            totalNet: ScalarCaster::toMoneyOrNull($data['total_net'] ?? null),
            netGross: NetGross::tryFrom(ScalarCaster::toStringOrNull($data['net_gross'] ?? null) ?? ''),
            reduction: ScalarCaster::toStringOrNull($data['reduction'] ?? null),
            totalGrossUnreduced: ScalarCaster::toMoneyOrNull($data['total_gross_unreduced'] ?? null),
            totalNetUnreduced: ScalarCaster::toMoneyOrNull($data['total_net_unreduced'] ?? null),
            currencyCode: ScalarCaster::toStringOrNull($data['currency_code'] ?? null),
            quote: ScalarCaster::toFloatOrNull($data['quote'] ?? null),
            offerId: ScalarCaster::toIntOrNull($data['offer_id'] ?? null),
            freeTextId: ScalarCaster::toIntOrNull($data['free_text_id'] ?? null),
            templateId: ScalarCaster::toIntOrNull($data['template_id'] ?? null),
            taxes: ListCaster::toList($data['taxes']['tax'] ?? null, 'name', TaxSummary::fromArray(...)),
            items: ListCaster::toList($data['confirmation-items']['confirmation-item'] ?? null, 'id', ConfirmationItem::fromArray(...)),
            updated: ScalarCaster::toDateTimeOrNull($data['updated'] ?? null),
            customfield: ScalarCaster::toStringOrNull($data['customfield'] ?? null),
            totalReduction: ScalarCaster::toMoneyOrNull($data['total_reduction'] ?? null),
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
        $data = [
            ...$this->extra,
            'id' => $this->id,
            'client_id' => $this->clientId,
            'contact_id' => $this->contactId,
            'created' => $this->created?->format(DATE_ATOM),
            'confirmation_number' => $this->confirmationNumber,
            'number' => $this->number,
            'number_pre' => $this->numberPre,
            'number_length' => $this->numberLength,
            'status' => $this->status?->value,
            'date' => $this->date?->format('Y-m-d'),
            'address' => $this->address,
            'title' => $this->title,
            'label' => $this->label,
            'intro' => $this->intro,
            'note' => $this->note,
            'total_gross' => $this->totalGross?->toString(),
            'total_net' => $this->totalNet?->toString(),
            'net_gross' => $this->netGross?->value,
            'reduction' => $this->reduction,
            'total_gross_unreduced' => $this->totalGrossUnreduced?->toString(),
            'total_net_unreduced' => $this->totalNetUnreduced?->toString(),
            'currency_code' => $this->currencyCode,
            'quote' => $this->quote,
            'offer_id' => $this->offerId,
            'free_text_id' => $this->freeTextId,
            'template_id' => $this->templateId,
            'updated' => $this->updated?->format(DATE_ATOM),
            'customfield' => $this->customfield,
            'total_reduction' => $this->totalReduction?->toString(),
        ];

        if ([] !== $this->taxes) {
            $data['taxes'] = [
                'tax' => array_map(static fn (TaxSummary $tax): array => $tax->toArray(), $this->taxes),
            ];
        }

        if ([] !== $this->items) {
            $data['confirmation-items'] = [
                'confirmation-item' => array_map(static fn (ConfirmationItem $item): array => $item->toArray(), $this->items),
            ];
        }

        return $data;
    }
}
