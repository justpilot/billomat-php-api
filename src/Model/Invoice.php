<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use DateTimeImmutable;
use Justpilot\Billomat\Internal\ListCaster;
use Justpilot\Billomat\Internal\ScalarCaster;
use Justpilot\Billomat\Model\Enum\InvoiceStatus;
use Justpilot\Billomat\Model\Enum\NetGross;
use Justpilot\Billomat\Model\Enum\SupplyDateType;

use const DATE_ATOM;

/**
 * Repräsentiert eine Rechnung aus der Billomat-API.
 *
 * Status ist bei Erstellung immer DRAFT. Die endgültige Rechnungsnummer
 * (invoice_number) wird erst beim Abschluss der Rechnung gesetzt.
 *
 * Die meisten Felder sind optional und hängen davon ab,
 * welcher Endpoint genutzt wurde und welche Einstellungen im Account aktiv sind.
 *
 * Dokumentation:
 * https://www.billomat.com/en/api/invoices/
 *
 * Generiert aus tools/codegen/definitions/invoices.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final readonly class Invoice
{
    /**
     * @param list<array{name: string, rate: float, amount: float}> $taxes
     * @param list<InvoiceItem>                                     $items
     */
    public function __construct(
        /** Interne Billomat-ID der Rechnung. */
        public ?int $id,
        /** ID des Kunden. */
        public int $clientId,
        /** ID des Kontakts. */
        public ?int $contactId = null,
        /** Erstellungszeitpunkt der Rechnung. */
        public ?DateTimeImmutable $created = null,
        /** Rechnungsnummer (kann bei DRAFT leer sein). */
        public ?string $invoiceNumber = null,
        /** Laufende Nummer (ohne Präfix). */
        public ?int $number = null,
        /** Präfix der Rechnungsnummer (z. B. "RE"). */
        public ?string $numberPre = null,
        /** Mindestlänge der Rechnungsnummer (wird mit führenden Nullen aufgefüllt). */
        public ?int $numberLength = null,
        /** Status der Rechnung, z. B. DRAFT, OPEN, PAID. */
        public ?InvoiceStatus $status = null,
        /** Rechnungsdatum. */
        public ?DateTimeImmutable $date = null,
        /** Liefer-/Leistungsdatum. */
        public ?DateTimeImmutable $supplyDate = null,
        /** Typ des Liefer-/Leistungsdatums. */
        public ?SupplyDateType $supplyDateType = null,
        /** Fälligkeitsdatum. */
        public ?DateTimeImmutable $dueDate = null,
        /** Tage bis zur Fälligkeit. */
        public ?int $dueDays = null,
        /** Vollständige Rechnungsadresse (formatiert). */
        public ?string $address = null,
        /** Skonto in Prozent. */
        public ?float $discountRate = null,
        /** Skontodatum. */
        public ?DateTimeImmutable $discountDate = null,
        /** Skontofrist in Tagen. */
        public ?int $discountDays = null,
        /** Skontobetrag. */
        public ?float $discountAmount = null,
        /** Dokumentenüberschrift. */
        public ?string $title = null,
        /** Bezeichnung. */
        public ?string $label = null,
        /** Einleitungstext. */
        public ?string $intro = null,
        /** Anmerkungstext. */
        public ?string $note = null,
        /** Gesamtbetrag brutto. */
        public ?float $totalGross = null,
        /** Gesamtbetrag netto. */
        public ?float $totalNet = null,
        /** Preisbasis (NET, GROSS oder SETTINGS). */
        public ?NetGross $netGross = null,
        /** Rabatt (absolut oder als Prozentwert, z. B. "10" oder "10%"). */
        public ?string $reduction = null,
        /** Gesamtbetrag brutto vor Rabatt. */
        public ?float $totalGrossUnreduced = null,
        /** Gesamtbetrag netto vor Rabatt. */
        public ?float $totalNetUnreduced = null,
        /** Bereits bezahlter Betrag. */
        public ?float $paidAmount = null,
        /** Offener Betrag. */
        public ?float $openAmount = null,
        /** Währung (ISO-Währungscode, z. B. "EUR"). */
        public ?string $currencyCode = null,
        /** Währungskurs (für Umrechnung in die Standard-Währung). */
        public ?float $quote = null,
        /** ID der korrigierten Rechnung (bei Korrekturrechnung). */
        public ?int $invoiceId = null,
        /** ID des Angebots, aus dem die Rechnung entstanden ist. */
        public ?int $offerId = null,
        /** ID der Auftragsbestätigung, aus der die Rechnung entstanden ist. */
        public ?int $confirmationId = null,
        /** ID der Abo-Rechnung, aus der die Rechnung entstanden ist. */
        public ?int $recurringId = null,
        /** Steuerzeilen der Rechnung (Zusammenfassung pro Steuersatz). */
        public array $taxes = [],
        /**
         * Akzeptierte Zahlungsarten (kommasepariert).
         *
         * Beispiel: "BANK_TRANSFER,CASH"
         */
        public ?string $paymentTypes = null,
        /**
         * URL zum Customer-Portal für diese Rechnung.
         *
         * @deprecated since 3.1 – Billomat liefert `customerportal_url` nicht (mehr) aus (Stand 2026-10), der Wert ist immer `null`. Wird in 4.0 entfernt.
         */
        public ?string $customerportalUrl = null,
        /** ID der Vorlage, mit der die Rechnung abgeschlossen wird. */
        public ?int $templateId = null,
        /** Positionen der Rechnung (nur bei GET /invoices/{id}). */
        public array $items = [],
        /** Zeitpunkt der letzten Änderung. */
        public ?DateTimeImmutable $updated = null,
        /** Freies Zusatzfeld (siehe Custom-Meta-Attribute). */
        public ?string $customfield = null,
        /** Referenz der Factur-X-/ZUGFeRD-Rechnung. */
        public ?string $facturxReference = null,
        /** Summe der Rabatte. */
        public ?float $totalReduction = null,
        /** Wurde die Rechnung digital weiterverarbeitet? */
        public ?bool $digProceeded = null,
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
            invoiceNumber: ScalarCaster::toStringOrNull($data['invoice_number'] ?? null),
            number: ScalarCaster::toIntOrNull($data['number'] ?? null),
            numberPre: ScalarCaster::toStringOrNull($data['number_pre'] ?? null),
            numberLength: ScalarCaster::toIntOrNull($data['number_length'] ?? null),
            status: InvoiceStatus::tryFrom(ScalarCaster::toStringOrNull($data['status'] ?? null) ?? ''),
            date: ScalarCaster::toDateTimeOrNull($data['date'] ?? null),
            supplyDate: ScalarCaster::toDateTimeOrNull($data['supply_date'] ?? null),
            supplyDateType: SupplyDateType::tryFrom(ScalarCaster::toStringOrNull($data['supply_date_type'] ?? null) ?? ''),
            dueDate: ScalarCaster::toDateTimeOrNull($data['due_date'] ?? null),
            dueDays: ScalarCaster::toIntOrNull($data['due_days'] ?? null),
            address: ScalarCaster::toStringOrNull($data['address'] ?? null),
            discountRate: ScalarCaster::toFloatOrNull($data['discount_rate'] ?? null),
            discountDate: ScalarCaster::toDateTimeOrNull($data['discount_date'] ?? null),
            discountDays: ScalarCaster::toIntOrNull($data['discount_days'] ?? null),
            discountAmount: ScalarCaster::toFloatOrNull($data['discount_amount'] ?? null),
            title: ScalarCaster::toStringOrNull($data['title'] ?? null),
            label: ScalarCaster::toStringOrNull($data['label'] ?? null),
            intro: ScalarCaster::toStringOrNull($data['intro'] ?? null),
            note: ScalarCaster::toStringOrNull($data['note'] ?? null),
            totalGross: ScalarCaster::toFloatOrNull($data['total_gross'] ?? null),
            totalNet: ScalarCaster::toFloatOrNull($data['total_net'] ?? null),
            netGross: NetGross::tryFrom(ScalarCaster::toStringOrNull($data['net_gross'] ?? null) ?? ''),
            reduction: ScalarCaster::toStringOrNull($data['reduction'] ?? null),
            totalGrossUnreduced: ScalarCaster::toFloatOrNull($data['total_gross_unreduced'] ?? null),
            totalNetUnreduced: ScalarCaster::toFloatOrNull($data['total_net_unreduced'] ?? null),
            paidAmount: ScalarCaster::toFloatOrNull($data['paid_amount'] ?? null),
            openAmount: ScalarCaster::toFloatOrNull($data['open_amount'] ?? null),
            currencyCode: ScalarCaster::toStringOrNull($data['currency_code'] ?? null),
            quote: ScalarCaster::toFloatOrNull($data['quote'] ?? null),
            invoiceId: ScalarCaster::toIntOrNull($data['invoice_id'] ?? null),
            offerId: ScalarCaster::toIntOrNull($data['offer_id'] ?? null),
            confirmationId: ScalarCaster::toIntOrNull($data['confirmation_id'] ?? null),
            recurringId: ScalarCaster::toIntOrNull($data['recurring_id'] ?? null),
            taxes: ListCaster::toTaxList($data['taxes']['tax'] ?? null),
            paymentTypes: ScalarCaster::toStringOrNull($data['payment_types'] ?? null),
            customerportalUrl: ScalarCaster::toStringOrNull($data['customerportal_url'] ?? null),
            templateId: ScalarCaster::toIntOrNull($data['template_id'] ?? null),
            items: ListCaster::toList($data['invoice-items']['invoice-item'] ?? null, 'id', InvoiceItem::fromArray(...)),
            updated: ScalarCaster::toDateTimeOrNull($data['updated'] ?? null),
            customfield: ScalarCaster::toStringOrNull($data['customfield'] ?? null),
            facturxReference: ScalarCaster::toStringOrNull($data['facturx_reference'] ?? null),
            totalReduction: ScalarCaster::toFloatOrNull($data['total_reduction'] ?? null),
            digProceeded: ScalarCaster::toBoolOrNull($data['dig_proceeded'] ?? null),
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
            'invoice_number' => $this->invoiceNumber,
            'number' => $this->number,
            'number_pre' => $this->numberPre,
            'number_length' => $this->numberLength,
            'status' => $this->status?->value,
            'date' => $this->date?->format('Y-m-d'),
            'supply_date' => $this->supplyDate?->format('Y-m-d'),
            'supply_date_type' => $this->supplyDateType?->value,
            'due_date' => $this->dueDate?->format('Y-m-d'),
            'due_days' => $this->dueDays,
            'address' => $this->address,
            'discount_rate' => $this->discountRate,
            'discount_date' => $this->discountDate?->format('Y-m-d'),
            'discount_days' => $this->discountDays,
            'discount_amount' => $this->discountAmount,
            'title' => $this->title,
            'label' => $this->label,
            'intro' => $this->intro,
            'note' => $this->note,
            'total_gross' => $this->totalGross,
            'total_net' => $this->totalNet,
            'net_gross' => $this->netGross?->value,
            'reduction' => $this->reduction,
            'total_gross_unreduced' => $this->totalGrossUnreduced,
            'total_net_unreduced' => $this->totalNetUnreduced,
            'paid_amount' => $this->paidAmount,
            'open_amount' => $this->openAmount,
            'currency_code' => $this->currencyCode,
            'quote' => $this->quote,
            'invoice_id' => $this->invoiceId,
            'offer_id' => $this->offerId,
            'confirmation_id' => $this->confirmationId,
            'recurring_id' => $this->recurringId,
            'payment_types' => $this->paymentTypes,
            'customerportal_url' => $this->customerportalUrl,
            'template_id' => $this->templateId,
            'updated' => $this->updated?->format(DATE_ATOM),
            'customfield' => $this->customfield,
            'facturx_reference' => $this->facturxReference,
            'total_reduction' => $this->totalReduction,
            'dig_proceeded' => $this->digProceeded,
        ];

        if ([] !== $this->taxes) {
            $data['taxes'] = ['tax' => $this->taxes];
        }

        if ([] !== $this->items) {
            $data['invoice-items'] = [
                'invoice-item' => array_map(static fn (InvoiceItem $item): array => $item->toArray(), $this->items),
            ];
        }

        return $data;
    }
}
