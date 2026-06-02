<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use DateTimeImmutable;
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
        /** ID des Kontakts (optional). */
        public ?int $contactId = null,
        /** Erstellungszeitpunkt der Rechnung. */
        public ?DateTimeImmutable $created = null,
        /** Rechnungsnummer (kann bei DRAFT leer sein). */
        public ?string $invoiceNumber = null,
        /** Laufende Nummer (ohne Präfix). */
        public ?int $number = null,
        /** Präfix (z. B. "RE"). */
        public ?string $numberPre = null,
        /** Mindestlänge der Rechnungsnummer. */
        public ?int $numberLength = null,
        /** Status, z. B. DRAFT, OPEN, PAID. */
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
        /** Bezeichnung / Label. */
        public ?string $label = null,
        /** Einleitungstext. */
        public ?string $intro = null,
        /** Anmerkungstext. */
        public ?string $note = null,
        /** Bruttosumme der Rechnung. */
        public ?float $totalGross = null,
        /** Nettosumme der Rechnung. */
        public ?float $totalNet = null,
        /** Preisbasis (NET, GROSS oder SETTINGS). */
        public ?NetGross $netGross = null,
        /** Gesamtrabatt (z. B. "10" oder "10%"). */
        public ?string $reduction = null,
        /** Bruttosumme ohne Rabatt. */
        public ?float $totalGrossUnreduced = null,
        /** Nettosumme ohne Rabatt. */
        public ?float $totalNetUnreduced = null,
        /** Bereits bezahlter Betrag. */
        public ?float $paidAmount = null,
        /** Offener Betrag. */
        public ?float $openAmount = null,
        /** Währungscode, z. B. "EUR". */
        public ?string $currencyCode = null,
        /** Währungskurs. */
        public ?float $quote = null,
        /** ID der korrigierten Rechnung (bei Korrekturrechnung). */
        public ?int $invoiceId = null,
        /** ID des Angebots, aus dem die Rechnung entstanden ist. */
        public ?int $offerId = null,
        /** ID der Auftragsbestätigung, aus der die Rechnung entstanden ist. */
        public ?int $confirmationId = null,
        /** ID der Abo-Rechnung, aus der die Rechnung entstanden ist. */
        public ?int $recurringId = null,
        /**
         * Steuerzusammenfassung pro Steuersatz.
         *
         * Struktur:
         * [
         *     ['name' => string, 'rate' => float, 'amount' => float],
         *     ...
         * ]
         */
        public array $taxes = [],
        /** Akzeptierte Zahlungsarten (kommasepariert, z. B. "CASH,BANK_TRANSFER"). */
        public ?string $paymentTypes = null,
        /** URL zum Customer-Portal für diese Rechnung. */
        public ?string $customerportalUrl = null,
        /** ID der Vorlage, mit der die Rechnung erzeugt/abgeschlossen wurde. */
        public ?int $templateId = null,
        /**
         * Rechnungspositionen, falls im API-Response enthalten.
         */
        public array $items = []
    ) {
    }

    /**
     * Hydriert eine Invoice aus einem Billomat-Array.
     *
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        // Taxes
        $taxes = [];
        if (isset($data['taxes']['tax'])) {
            $rawTaxes = $data['taxes']['tax'];

            // Einzelner Eintrag → normalisieren auf Liste
            if (isset($rawTaxes['name'])) {
                $rawTaxes = [$rawTaxes];
            }

            if (\is_array($rawTaxes)) {
                foreach ($rawTaxes as $taxRow) {
                    if (!\is_array($taxRow)) {
                        continue;
                    }

                    $taxes[] = [
                        'name' => (string) ($taxRow['name'] ?? ''),
                        'rate' => isset($taxRow['rate']) ? (float) $taxRow['rate'] : 0.0,
                        'amount' => isset($taxRow['amount']) ? (float) $taxRow['amount'] : 0.0,
                    ];
                }
            }
        }

        // Invoice-Items, falls vorhanden
        $items = [];
        if (isset($data['invoice-items']['invoice-item'])) {
            $rawItems = $data['invoice-items']['invoice-item'];

            if (isset($rawItems['id'])) {
                $rawItems = [$rawItems];
            }

            if (\is_array($rawItems)) {
                $items = array_map(
                    InvoiceItem::fromArray(...),
                    $rawItems,
                );
            }
        }

        return new self(
            id: ScalarCaster::toIntOrNull($data['id'] ?? null),
            clientId: (int) ($data['client_id'] ?? 0),
            contactId: ScalarCaster::toIntOrNull($data['contact_id'] ?? null),
            created: ScalarCaster::toDateTimeOrNull($data['created'] ?? null),
            invoiceNumber: ScalarCaster::toStringOrNull($data['invoice_number'] ?? null),
            number: ScalarCaster::toIntOrNull($data['number'] ?? null),
            numberPre: ScalarCaster::toStringOrNull($data['number_pre'] ?? null),
            numberLength: ScalarCaster::toIntOrNull($data['number_length'] ?? null),
            status: InvoiceStatus::fromApi($data['status'] ?? null),
            date: ScalarCaster::toDateTimeOrNull($data['date'] ?? null),
            supplyDate: ScalarCaster::toDateTimeOrNull($data['supply_date'] ?? null),
            supplyDateType: isset($data['supply_date_type'])
                ? SupplyDateType::tryFrom((string) $data['supply_date_type'])
                : null,
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
            netGross: isset($data['net_gross'])
                ? NetGross::tryFrom((string) $data['net_gross'])
                : null,
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
            taxes: $taxes,
            paymentTypes: ScalarCaster::toStringOrNull($data['payment_types'] ?? null),
            customerportalUrl: ScalarCaster::toStringOrNull($data['customerportal_url'] ?? null),
            templateId: ScalarCaster::toIntOrNull($data['template_id'] ?? null),
            items: $items,
        );
    }

    /**
     * Exportiert die Rechnung als Array mit Billomat-Feldnamen.
     *
     * Hinweis: Nicht alle Felder sind für POST/PUT gedacht,
     * dieses Array eignet sich eher für Debug/Logging oder interne Weitergabe.
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
        ];

        if ([] !== $this->taxes) {
            $data['taxes'] = [
                'tax' => array_map(
                    static fn (array $t): array => [
                        'name' => $t['name'],
                        'rate' => $t['rate'],
                        'amount' => $t['amount'],
                    ],
                    $this->taxes,
                ),
            ];
        }

        if ([] !== $this->items) {
            $data['invoice-items'] = [
                'invoice-item' => array_map(
                    static fn (InvoiceItem $item): array => $item->toArray(),
                    $this->items,
                ),
            ];
        }

        return $data;
    }
}
