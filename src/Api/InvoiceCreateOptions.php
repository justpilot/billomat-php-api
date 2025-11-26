<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

/**
 * Typisierter Payload für POST /invoices gemäß Billomat-API.
 *
 * Dokumentation:
 * https://www.billomat.com/api/invoices/
 *
 * Null-Werte bedeuten "nicht gesetzt" und werden beim Serialisieren ignoriert,
 * sodass Billomat seine Defaults verwenden kann.
 */
final class InvoiceCreateOptions
{
    /**
     * ID des Kunden (client_id).
     *
     * Billomat-Feld: client_id
     * Typ: INT
     * Pflicht: ja
     */
    public int $clientId;

    public function __construct(int $clientId)
    {
        $this->clientId = $clientId;
    }

    /**
     * ID des Kontakts.
     *
     * Billomat-Feld: contact_id
     * Typ: INT
     */
    public ?int $contactId = null;

    /**
     * Komplette Rechnungsadresse.
     *
     * Billomat-Feld: address
     * Typ: ALNUM
     * Default: Adresse des Kunden
     */
    public ?string $address = null;

    /**
     * Präfix der Rechnungsnummer.
     *
     * Billomat-Feld: number_pre
     * Typ: ALNUM
     * Default: Wert aus Einstellungen
     */
    public ?string $numberPre = null;

    /**
     * Laufende Rechnungsnummer.
     *
     * Billomat-Feld: number
     * Typ: INT
     * Default: nächste freie Nummer
     */
    public ?int $number = null;

    /**
     * Mindestlänge der Rechnungsnummer (mit führenden Nullen).
     *
     * Billomat-Feld: number_length
     * Typ: INT
     * Default: Wert aus Einstellungen
     */
    public ?int $numberLength = null;

    /**
     * Rechnungsdatum (YYYY-MM-DD).
     *
     * Billomat-Feld: date
     * Typ: DATE
     * Default: heute
     */
    public ?\DateTimeImmutable $date = null;

    /**
     * Liefer-/Leistungsdatum.
     *
     * Billomat-Feld: supply_date
     * Typ: MIXED (DATE/ALNUM)
     */
    public ?\DateTimeImmutable $supplyDate = null;

    /**
     * Typ des Liefer-/Leistungsdatums.
     *
     * Billomat-Feld: supply_date_type
     * Typ: ALNUM („SUPPLY_DATE“, „DELIVERY_DATE“, „SUPPLY_TEXT“, „DELIVERY_TEXT“)
     */
    public ?string $supplyDateType = null;

    /**
     * Tage bis Fälligkeit.
     *
     * Billomat-Feld: due_days
     * Typ: INT
     * Default: Fälligkeit aus Einstellungen
     */
    public ?int $dueDays = null;

    /**
     * Fälligkeitsdatum (YYYY-MM-DD).
     *
     * Billomat-Feld: due_date
     * Typ: DATE
     * Default: date + due_days
     */
    public ?\DateTimeImmutable $dueDate = null;

    /**
     * Skonto in Prozent.
     *
     * Billomat-Feld: discount_rate
     * Typ: INT
     * Default: Wert aus Einstellungen
     */
    public ?int $discountRate = null;

    /**
     * Skontofrist in Tagen.
     *
     * Billomat-Feld: discount_days
     * Typ: INT
     * Default: Wert aus Einstellungen
     */
    public ?int $discountDays = null;

    /**
     * Skontodatum (YYYY-MM-DD).
     *
     * Billomat-Feld: discount_date
     * Typ: DATE
     * Default: date + discount_days
     */
    public ?\DateTimeImmutable $discountDate = null;

    /**
     * Dokumentenüberschrift.
     *
     * Billomat-Feld: title
     * Typ: ALNUM
     */
    public ?string $title = null;

    /**
     * Bezeichnung.
     *
     * Billomat-Feld: label
     * Typ: ALNUM
     */
    public ?string $label = null;

    /**
     * Einleitungstext.
     *
     * Billomat-Feld: intro
     * Typ: ALNUM
     * Default: Wert aus Einstellungen
     */
    public ?string $intro = null;

    /**
     * Anmerkungstext.
     *
     * Billomat-Feld: note
     * Typ: ALNUM
     */
    public ?string $note = null;

    /**
     * Rabatt (absolut oder als Prozentwert, z.B. "10" oder "10%").
     *
     * Billomat-Feld: reduction
     * Typ: ALNUM
     */
    public ?string $reduction = null;

    /**
     * Währungscode.
     *
     * Billomat-Feld: currency_code
     * Typ: ISO-Währungscode
     * Default: Standard-Währung
     */
    public ?string $currencyCode = null;

    /**
     * Preisbasis (NET oder GROSS).
     *
     * Billomat-Feld: net_gross
     * Typ: ALNUM („NET“, „GROSS“)
     * Default: Wert aus Einstellungen
     */
    public ?string $netGross = null;

    /**
     * Währungskurs.
     *
     * Billomat-Feld: quote
     * Typ: FLOAT
     * Default: 1.0000
     */
    public ?float $quote = null;

    /**
     * Akzeptierte Zahlungsarten (kommasepariert).
     *
     * Billomat-Feld: payment_types
     * Typ: ALNUM
     * Default: Wert aus Einstellungen
     *
     * Beispiel: "BANK_TRANSFER,CASH"
     */
    public ?string $paymentTypes = null;

    /**
     * ID der korrigierten Rechnung (für Korrekturrechnung).
     *
     * Billomat-Feld: invoice_id
     * Typ: INT
     */
    public ?int $invoiceId = null;

    /**
     * ID des Angebots, aus dem die Rechnung entstanden ist.
     *
     * Billomat-Feld: offer_id
     * Typ: INT
     */
    public ?int $offerId = null;

    /**
     * ID der Auftragsbestätigung, aus der die Rechnung entstanden ist.
     *
     * Billomat-Feld: confirmation_id
     * Typ: INT
     */
    public ?int $confirmationId = null;

    /**
     * ID der Abo-Rechnung, aus der die Rechnung entstanden ist.
     *
     * Billomat-Feld: recurring_id
     * Typ: INT
     */
    public ?int $recurringId = null;

    /**
     * ID des Freitextes (für title, label, intro, note).
     *
     * Billomat-Feld: free_text_id
     * Typ: INT
     */
    public ?int $freeTextId = null;

    /**
     * ID der Vorlage, mit der die Rechnung abgeschlossen werden soll.
     *
     * Billomat-Feld: template_id
     * Typ: INT
     * Default: ID der Standardvorlage
     */
    public ?int $templateId = null;

    /**
     * @var list<InvoiceItemCreateOptions>
     */
    private array $items = [];

    /**
     * Fügt eine Rechnungsposition hinzu.
     *
     * @return $this
     */
    public function addItem(InvoiceItemCreateOptions $item): self
    {
        $this->items[] = $item;

        return $this;
    }

    /**
     * @return list<InvoiceItemCreateOptions>
     */
    public function getItems(): array
    {
        return $this->items;
    }

    // in toArray() am Ende:
    public function toArray(): array
    {
        $data = [
            'client_id' => $this->clientId,
            'contact_id' => $this->contactId,
            'address' => $this->address,
            'number_pre' => $this->numberPre,
            'number' => $this->number,
            'number_length' => $this->numberLength,
            'date' => $this->date?->format('Y-m-d'),
            'supply_date' => $this->supplyDate?->format('Y-m-d'),
            'supply_date_type' => $this->supplyDateType,
            'due_days' => $this->dueDays,
            'due_date' => $this->dueDate,
            'discount_rate' => $this->discountRate,
            'discount_days' => $this->discountDays,
            'discount_date' => $this->discountDate,
            'title' => $this->title,
            'label' => $this->label,
            'intro' => $this->intro,
            'note' => $this->note,
            'reduction' => $this->reduction,
            'currency_code' => $this->currencyCode,
            'net_gross' => $this->netGross,
            'quote' => $this->quote,
            'payment_types' => $this->paymentTypes,
            'invoice_id' => $this->invoiceId,
            'offer_id' => $this->offerId,
            'confirmation_id' => $this->confirmationId,
            'recurring_id' => $this->recurringId,
            'free_text_id' => $this->freeTextId,
            'template_id' => $this->templateId,
        ];

        $data = array_filter($data, static fn($v) => $v !== null);

        if ($this->items !== []) {
            $data['invoice-items'] = [
                'invoice-item' => array_map(
                    static fn(InvoiceItemCreateOptions $item): array => $item->toArray(),
                    $this->items
                ),
            ];
        }

        return $data;
    }
}