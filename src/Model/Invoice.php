<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use Justpilot\Billomat\Model\Enum\InvoiceStatus;
use Justpilot\Billomat\Model\Enum\NetGross;
use Justpilot\Billomat\Model\Enum\SupplyDateType;

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
    /** Interne Billomat-ID der Rechnung. */
    public ?int $id;

    /** ID des Kunden. */
    public int $clientId;

    /** ID des Kontakts (optional). */
    public ?int $contactId;

    /** Erstellungszeitpunkt der Rechnung. */
    public ?\DateTimeImmutable $created;

    /** Rechnungsnummer (kann bei DRAFT leer sein). */
    public ?string $invoiceNumber;

    /** Laufende Nummer (ohne Präfix). */
    public ?int $number;

    /** Präfix (z. B. "RE"). */
    public ?string $numberPre;

    /** Mindestlänge der Rechnungsnummer. */
    public ?int $numberLength;

    /** Status, z. B. DRAFT, OPEN, PAID. */
    public ?InvoiceStatus $status;

    /** Rechnungsdatum. */
    public ?\DateTimeImmutable $date;

    /** Liefer-/Leistungsdatum. */
    public ?\DateTimeImmutable $supplyDate;

    /** Typ des Liefer-/Leistungsdatums. */
    public ?SupplyDateType $supplyDateType;

    /** Fälligkeitsdatum. */
    public ?\DateTimeImmutable $dueDate;

    /** Tage bis zur Fälligkeit. */
    public ?int $dueDays;

    /** Vollständige Rechnungsadresse (formatiert). */
    public ?string $address;

    /** Skonto in Prozent. */
    public ?float $discountRate;

    /** Skontodatum. */
    public ?\DateTimeImmutable $discountDate;

    /** Skontofrist in Tagen. */
    public ?int $discountDays;

    /** Skontobetrag. */
    public ?float $discountAmount;

    /** Dokumentenüberschrift. */
    public ?string $title;

    /** Bezeichnung / Label. */
    public ?string $label;

    /** Einleitungstext. */
    public ?string $intro;

    /** Anmerkungstext. */
    public ?string $note;

    /** Bruttosumme der Rechnung. */
    public ?float $totalGross;

    /** Nettosumme der Rechnung. */
    public ?float $totalNet;

    /** Preisbasis (NET, GROSS oder SETTINGS). */
    public ?NetGross $netGross;

    /** Gesamtrabatt (z. B. "10" oder "10%"). */
    public ?string $reduction;

    /** Bruttosumme ohne Rabatt. */
    public ?float $totalGrossUnreduced;

    /** Nettosumme ohne Rabatt. */
    public ?float $totalNetUnreduced;

    /** Bereits bezahlter Betrag. */
    public ?float $paidAmount;

    /** Offener Betrag. */
    public ?float $openAmount;

    /** Währungscode, z. B. "EUR". */
    public ?string $currencyCode;

    /** Währungskurs. */
    public ?float $quote;

    /** ID der korrigierten Rechnung (bei Korrekturrechnung). */
    public ?int $invoiceId;

    /** ID des Angebots, aus dem die Rechnung entstanden ist. */
    public ?int $offerId;

    /** ID der Auftragsbestätigung, aus der die Rechnung entstanden ist. */
    public ?int $confirmationId;

    /** ID der Abo-Rechnung, aus der die Rechnung entstanden ist. */
    public ?int $recurringId;

    /**
     * Steuerzusammenfassung pro Steuersatz.
     *
     * Struktur:
     * [
     *     ['name' => string, 'rate' => float, 'amount' => float],
     *     ...
     * ]
     *
     * @var list<array{name: string, rate: float, amount: float}>
     */
    public array $taxes;

    /** Akzeptierte Zahlungsarten (kommasepariert, z. B. "CASH,BANK_TRANSFER"). */
    public ?string $paymentTypes;

    /** URL zum Customer-Portal für diese Rechnung. */
    public ?string $customerportalUrl;

    /** ID der Vorlage, mit der die Rechnung erzeugt/abgeschlossen wurde. */
    public ?int $templateId;

    /**
     * Rechnungspositionen, falls im API-Response enthalten.
     *
     * @var list<InvoiceItem>
     */
    public array $items;

    /**
     * @param list<array{name: string, rate: float, amount: float}> $taxes
     * @param list<InvoiceItem> $items
     */
    public function __construct(
        ?int                $id,
        int                 $clientId,
        ?int                $contactId = null,
        ?\DateTimeImmutable $created = null,
        ?string             $invoiceNumber = null,
        ?int                $number = null,
        ?string             $numberPre = null,
        ?int                $numberLength = null,
        ?InvoiceStatus      $status = null,
        ?\DateTimeImmutable $date = null,
        ?\DateTimeImmutable $supplyDate = null,
        ?SupplyDateType     $supplyDateType = null,
        ?\DateTimeImmutable $dueDate = null,
        ?int                $dueDays = null,
        ?string             $address = null,
        ?float              $discountRate = null,
        ?\DateTimeImmutable $discountDate = null,
        ?int                $discountDays = null,
        ?float              $discountAmount = null,
        ?string             $title = null,
        ?string             $label = null,
        ?string             $intro = null,
        ?string             $note = null,
        ?float              $totalGross = null,
        ?float              $totalNet = null,
        ?NetGross           $netGross = null,
        ?string             $reduction = null,
        ?float              $totalGrossUnreduced = null,
        ?float              $totalNetUnreduced = null,
        ?float              $paidAmount = null,
        ?float              $openAmount = null,
        ?string             $currencyCode = null,
        ?float              $quote = null,
        ?int                $invoiceId = null,
        ?int                $offerId = null,
        ?int                $confirmationId = null,
        ?int                $recurringId = null,
        array               $taxes = [],
        ?string             $paymentTypes = null,
        ?string             $customerportalUrl = null,
        ?int                $templateId = null,
        array               $items = [],
    )
    {
        $this->id = $id;
        $this->clientId = $clientId;
        $this->contactId = $contactId;
        $this->created = $created;
        $this->invoiceNumber = $invoiceNumber;
        $this->number = $number;
        $this->numberPre = $numberPre;
        $this->numberLength = $numberLength;
        $this->status = $status;
        $this->date = $date;
        $this->supplyDate = $supplyDate;
        $this->supplyDateType = $supplyDateType;
        $this->dueDate = $dueDate;
        $this->dueDays = $dueDays;
        $this->address = $address;
        $this->discountRate = $discountRate;
        $this->discountDate = $discountDate;
        $this->discountDays = $discountDays;
        $this->discountAmount = $discountAmount;
        $this->title = $title;
        $this->label = $label;
        $this->intro = $intro;
        $this->note = $note;
        $this->totalGross = $totalGross;
        $this->totalNet = $totalNet;
        $this->netGross = $netGross;
        $this->reduction = $reduction;
        $this->totalGrossUnreduced = $totalGrossUnreduced;
        $this->totalNetUnreduced = $totalNetUnreduced;
        $this->paidAmount = $paidAmount;
        $this->openAmount = $openAmount;
        $this->currencyCode = $currencyCode;
        $this->quote = $quote;
        $this->invoiceId = $invoiceId;
        $this->offerId = $offerId;
        $this->confirmationId = $confirmationId;
        $this->recurringId = $recurringId;
        $this->taxes = $taxes;
        $this->paymentTypes = $paymentTypes;
        $this->customerportalUrl = $customerportalUrl;
        $this->templateId = $templateId;
        $this->items = $items;
    }

    /**
     * Hydriert eine Invoice aus einem Billomat-Array.
     *
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $created = self::parseDateTime($data['created'] ?? null);
        $date = self::parseDateTime($data['date'] ?? null);
        $supplyDate = self::parseDateTime($data['supply_date'] ?? null);
        $dueDate = self::parseDateTime($data['due_date'] ?? null);
        $discountDate = self::parseDateTime($data['discount_date'] ?? null);

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
                        'name' => (string)($taxRow['name'] ?? ''),
                        'rate' => isset($taxRow['rate']) ? (float)$taxRow['rate'] : 0.0,
                        'amount' => isset($taxRow['amount']) ? (float)$taxRow['amount'] : 0.0,
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
                    static fn(array $row): InvoiceItem => InvoiceItem::fromArray($row),
                    $rawItems,
                );
            }
        }

        return new self(
            id: isset($data['id']) ? (int)$data['id'] : null,
            clientId: (int)($data['client_id'] ?? 0),
            contactId: isset($data['contact_id']) && $data['contact_id'] !== ''
                ? (int)$data['contact_id']
                : null,
            created: $created,
            invoiceNumber: $data['invoice_number'] ?? null,
            number: isset($data['number']) && $data['number'] !== ''
                ? (int)$data['number']
                : null,
            numberPre: $data['number_pre'] ?? null,
            numberLength: isset($data['number_length']) ? (int)$data['number_length'] : null,
            status: InvoiceStatus::fromApi($data['status'] ?? null),
            date: $date,
            supplyDate: $supplyDate,
            supplyDateType: isset($data['supply_date_type'])
                ? SupplyDateType::tryFrom((string)$data['supply_date_type'])
                : null,
            dueDate: $dueDate,
            dueDays: isset($data['due_days']) ? (int)$data['due_days'] : null,
            address: $data['address'] ?? null,
            discountRate: isset($data['discount_rate']) ? (float)$data['discount_rate'] : null,
            discountDate: $discountDate,
            discountDays: isset($data['discount_days']) ? (int)$data['discount_days'] : null,
            discountAmount: isset($data['discount_amount']) ? (float)$data['discount_amount'] : null,
            title: $data['title'] ?? null,
            label: $data['label'] ?? null,
            intro: $data['intro'] ?? null,
            note: $data['note'] ?? null,
            totalGross: isset($data['total_gross']) ? (float)$data['total_gross'] : null,
            totalNet: isset($data['total_net']) ? (float)$data['total_net'] : null,
            netGross: isset($data['net_gross'])
                ? NetGross::tryFrom((string)$data['net_gross'])
                : null,
            reduction: $data['reduction'] ?? null,
            totalGrossUnreduced: isset($data['total_gross_unreduced'])
                ? (float)$data['total_gross_unreduced']
                : null,
            totalNetUnreduced: isset($data['total_net_unreduced'])
                ? (float)$data['total_net_unreduced']
                : null,
            paidAmount: isset($data['paid_amount']) ? (float)$data['paid_amount'] : null,
            openAmount: isset($data['open_amount']) ? (float)$data['open_amount'] : null,
            currencyCode: $data['currency_code'] ?? null,
            quote: isset($data['quote']) ? (float)$data['quote'] : null,
            invoiceId: isset($data['invoice_id']) && $data['invoice_id'] !== ''
                ? (int)$data['invoice_id']
                : null,
            offerId: isset($data['offer_id']) && $data['offer_id'] !== ''
                ? (int)$data['offer_id']
                : null,
            confirmationId: isset($data['confirmation_id']) && $data['confirmation_id'] !== ''
                ? (int)$data['confirmation_id']
                : null,
            recurringId: isset($data['recurring_id']) && $data['recurring_id'] !== ''
                ? (int)$data['recurring_id']
                : null,
            taxes: $taxes,
            paymentTypes: $data['payment_types'] ?? null,
            customerportalUrl: $data['customerportal_url'] ?? null,
            templateId: isset($data['template_id']) && $data['template_id'] !== ''
                ? (int)$data['template_id']
                : null,
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
            'created' => $this->created?->format(\DATE_ATOM),
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

        if ($this->taxes !== []) {
            $data['taxes'] = [
                'tax' => array_map(
                    static fn(array $t): array => [
                        'name' => $t['name'],
                        'rate' => $t['rate'],
                        'amount' => $t['amount'],
                    ],
                    $this->taxes,
                ),
            ];
        }

        if ($this->items !== []) {
            $data['invoice-items'] = [
                'invoice-item' => array_map(
                    static fn(InvoiceItem $item): array => $item->toArray(),
                    $this->items,
                ),
            ];
        }

        return $data;
    }

    private static function parseDateTime(null|string $value): ?\DateTimeImmutable
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            return new \DateTimeImmutable((string)$value);
        } catch (\Throwable) {
            return null;
        }
    }
}