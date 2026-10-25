<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use DateTimeImmutable;
use Justpilot\Billomat\Internal\ListCaster;
use Justpilot\Billomat\Internal\ScalarCaster;
use Justpilot\Billomat\Model\Enum\NetGross;
use Justpilot\Billomat\Model\Enum\OfferStatus;

use const DATE_ATOM;

/**
 * Repräsentiert ein Angebot (Offer / Estimate) aus der Billomat-API.
 *
 * Status ist bei Erstellung immer DRAFT. Die endgültige Angebotsnummer
 * (offer_number) wird erst beim Abschluss vergeben.
 *
 * Dokumentation:
 * https://www.billomat.com/en/api/estimates/
 *
 * Generiert aus tools/codegen/definitions/offers.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final readonly class Offer
{
    /**
     * @param list<array{name: string, rate: float, amount: Money}> $taxes
     * @param list<OfferItem>                                       $items
     */
    public function __construct(
        /** Interne Billomat-ID des Angebots. */
        public ?int $id,
        /** ID des Kunden. */
        public int $clientId,
        /** ID des Kontakts. */
        public ?int $contactId = null,
        /** Erstellungszeitpunkt des Angebots. */
        public ?DateTimeImmutable $created = null,
        /** Angebotsnummer (wird beim Abschließen vergeben). */
        public ?string $offerNumber = null,
        /** Laufende Nummer (ohne Präfix). */
        public ?int $number = null,
        /** Präfix der Angebotsnummer. */
        public ?string $numberPre = null,
        /** Mindestlänge der Angebotsnummer (wird mit führenden Nullen aufgefüllt). */
        public ?int $numberLength = null,
        /** Status des Angebots. */
        public ?OfferStatus $status = null,
        /** Angebotsdatum. */
        public ?DateTimeImmutable $date = null,
        /** Gültigkeit in Tagen (Angebotsgültigkeit). */
        public ?int $validityDays = null,
        /** Vollständige Angebotsadresse (formatiert). */
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
        /** ID des Freitextes zur Belegung von title, label, intro und note. */
        public ?int $freeTextId = null,
        /** ID der Vorlage, mit der das Angebot abgeschlossen wird. */
        public ?int $templateId = null,
        /** Steuerzeilen des Angebots. */
        public array $taxes = [],
        /** Positionen des Angebots (nur bei GET /offers/{id}). */
        public array $items = [],
        /** Zeitpunkt der letzten Änderung. */
        public ?DateTimeImmutable $updated = null,
        /** Freies Zusatzfeld (siehe Custom-Meta-Attribute). */
        public ?string $customfield = null,
        /**
         * Konkretes Ablaufdatum des Angebots.
         *
         * Wird übersteuert durch `validityDays`, wenn beide gesetzt sind.
         */
        public ?DateTimeImmutable $validityDate = null,
        /** Summe der Rabatte. */
        public ?Money $totalReduction = null,
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
            offerNumber: ScalarCaster::toStringOrNull($data['offer_number'] ?? null),
            number: ScalarCaster::toIntOrNull($data['number'] ?? null),
            numberPre: ScalarCaster::toStringOrNull($data['number_pre'] ?? null),
            numberLength: ScalarCaster::toIntOrNull($data['number_length'] ?? null),
            status: OfferStatus::tryFrom(ScalarCaster::toStringOrNull($data['status'] ?? null) ?? ''),
            date: ScalarCaster::toDateTimeOrNull($data['date'] ?? null),
            validityDays: ScalarCaster::toIntOrNull($data['validity_days'] ?? null),
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
            freeTextId: ScalarCaster::toIntOrNull($data['free_text_id'] ?? null),
            templateId: ScalarCaster::toIntOrNull($data['template_id'] ?? null),
            taxes: ListCaster::toTaxList($data['taxes']['tax'] ?? null),
            items: ListCaster::toList($data['offer-items']['offer-item'] ?? null, 'id', OfferItem::fromArray(...)),
            updated: ScalarCaster::toDateTimeOrNull($data['updated'] ?? null),
            customfield: ScalarCaster::toStringOrNull($data['customfield'] ?? null),
            validityDate: ScalarCaster::toDateTimeOrNull($data['validity_date'] ?? null),
            totalReduction: ScalarCaster::toMoneyOrNull($data['total_reduction'] ?? null),
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
            'id' => $this->id,
            'client_id' => $this->clientId,
            'contact_id' => $this->contactId,
            'created' => $this->created?->format(DATE_ATOM),
            'offer_number' => $this->offerNumber,
            'number' => $this->number,
            'number_pre' => $this->numberPre,
            'number_length' => $this->numberLength,
            'status' => $this->status?->value,
            'date' => $this->date?->format('Y-m-d'),
            'validity_days' => $this->validityDays,
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
            'free_text_id' => $this->freeTextId,
            'template_id' => $this->templateId,
            'updated' => $this->updated?->format(DATE_ATOM),
            'customfield' => $this->customfield,
            'validity_date' => $this->validityDate?->format('Y-m-d'),
            'total_reduction' => $this->totalReduction?->toString(),
        ];

        if ([] !== $this->taxes) {
            $data['taxes'] = [
                'tax' => array_map(static fn (array $tax): array => [...$tax, 'amount' => $tax['amount']->toString()], $this->taxes),
            ];
        }

        if ([] !== $this->items) {
            $data['offer-items'] = [
                'offer-item' => array_map(static fn (OfferItem $item): array => $item->toArray(), $this->items),
            ];
        }

        return $data;
    }
}
