<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use DateTimeImmutable;
use Justpilot\Billomat\Model\Enum\NetGross;
use Justpilot\Billomat\Model\Enum\SupplyDateType;

/**
 * Typisierter Payload für POST /invoices gemäß Billomat-API.
 *
 * Null-Werte bedeuten "nicht gesetzt" und werden beim Serialisieren ignoriert,
 * sodass Billomat seine Defaults verwenden kann.
 * Positionen lassen sich beim Anlegen über addItem() direkt mitsenden.
 *
 * Doku:
 * https://www.billomat.com/en/api/invoices/
 *
 * Generiert aus tools/codegen/definitions/invoices.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final class InvoiceCreateOptions
{
    public function __construct(
        /**
         * ID des Kunden.
         *
         * Billomat-Feld: client_id
         * Typ: INT
         */
        public int $clientId,
    ) {
    }

    /**
     * ID des Kontakts.
     *
     * Billomat-Feld: contact_id
     * Typ: INT
     */
    public ?int $contactId = null;

    /**
     * Laufende Nummer (ohne Präfix).
     *
     * Billomat-Feld: number
     * Typ: INT
     * Default: nächste freie Nummer
     */
    public ?int $number = null;

    /**
     * Präfix der Rechnungsnummer (z. B. "RE").
     *
     * Billomat-Feld: number_pre
     * Typ: ALNUM
     * Default: Wert aus Einstellungen
     */
    public ?string $numberPre = null;

    /**
     * Mindestlänge der Rechnungsnummer (wird mit führenden Nullen aufgefüllt).
     *
     * Billomat-Feld: number_length
     * Typ: INT
     * Default: Wert aus den Einstellungen
     */
    public ?int $numberLength = null;

    /**
     * Rechnungsdatum.
     *
     * Format: YYYY-MM-DD
     *
     * Billomat-Feld: date
     * Typ: DATE
     * Default: heute
     */
    public ?DateTimeImmutable $date = null;

    /**
     * Liefer-/Leistungsdatum.
     *
     * Format: YYYY-MM-DD
     *
     * Billomat-Feld: supply_date
     * Typ: MIXED (DATE/ALNUM)
     */
    public ?DateTimeImmutable $supplyDate = null;

    /**
     * Typ des Liefer-/Leistungsdatums.
     *
     * Billomat-Feld: supply_date_type
     * Typ: ALNUM („SUPPLY_DATE“, „DELIVERY_DATE“, „SUPPLY_TEXT“, „DELIVERY_TEXT“)
     */
    public ?SupplyDateType $supplyDateType = null;

    /**
     * Fälligkeitsdatum.
     *
     * Format: YYYY-MM-DD
     *
     * Billomat-Feld: due_date
     * Typ: DATE
     * Default: date + due_days
     */
    public ?DateTimeImmutable $dueDate = null;

    /**
     * Tage bis zur Fälligkeit.
     *
     * Billomat-Feld: due_days
     * Typ: INT
     * Default: Fälligkeit in Tagen aus Einstellungen
     */
    public ?int $dueDays = null;

    /**
     * Vollständige Rechnungsadresse (formatiert).
     *
     * Billomat-Feld: address
     * Typ: ALNUM
     * Default: Adresse des Kunden
     */
    public ?string $address = null;

    /**
     * Skonto in Prozent.
     *
     * Billomat-Feld: discount_rate
     * Typ: INT
     * Default: Wert aus Einstellungen
     */
    public ?int $discountRate = null;

    /**
     * Skontodatum.
     *
     * Format: YYYY-MM-DD
     *
     * Billomat-Feld: discount_date
     * Typ: DATE
     * Default: date + discount_days
     */
    public ?DateTimeImmutable $discountDate = null;

    /**
     * Skontofrist in Tagen.
     *
     * Billomat-Feld: discount_days
     * Typ: INT
     * Default: Skontozeitraum in Tagen aus Einstellungen
     */
    public ?int $discountDays = null;

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
     * Default: Wert aus Einstellungen
     */
    public ?string $note = null;

    /**
     * Preisbasis (NET, GROSS oder SETTINGS).
     *
     * Billomat-Feld: net_gross
     * Typ: ALNUM („NET“, „GROSS“)
     * Default: Wert aus den Einstellungen
     */
    public ?NetGross $netGross = null;

    /**
     * Rabatt (absolut oder als Prozentwert, z. B. "10" oder "10%").
     *
     * Billomat-Feld: reduction
     * Typ: ALNUM
     */
    public ?string $reduction = null;

    /**
     * Währung (ISO-Währungscode, z. B. "EUR").
     *
     * Billomat-Feld: currency_code
     * Typ: ISO-Währungscode
     * Default: Standard-Währung
     */
    public ?string $currencyCode = null;

    /**
     * Währungskurs (für Umrechnung in die Standard-Währung).
     *
     * Billomat-Feld: quote
     * Typ: FLOAT
     * Default: 1.0000
     */
    public ?float $quote = null;

    /**
     * ID der korrigierten Rechnung (bei Korrekturrechnung).
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
     * Akzeptierte Zahlungsarten (kommasepariert).
     *
     * Beispiel: "BANK_TRANSFER,CASH"
     *
     * Billomat-Feld: payment_types
     * Typ: ALNUM
     * Default: Wert aus den Einstellungen
     */
    public ?string $paymentTypes = null;

    /**
     * ID der Vorlage, mit der die Rechnung abgeschlossen wird.
     *
     * Billomat-Feld: template_id
     * Typ: INT
     * Default: ID der Standardvorlage
     */
    public ?int $templateId = null;

    /**
     * ID des Freitextes zur Belegung von title, label, intro und note.
     *
     * Billomat-Feld: free_text_id
     * Typ: INT
     */
    public ?int $freeTextId = null;

    /**
     * @var list<InvoiceItemCreateOptions>
     */
    private array $items = [];

    /**
     * Fügt eine Position hinzu, die beim Anlegen mitgesendet wird.
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

    /**
     * Serialisiert die Options in ein Billomat-Payload-Array.
     *
     * @return array<string,mixed>
     */
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
            'supply_date_type' => $this->supplyDateType?->value,
            'due_days' => $this->dueDays,
            'due_date' => $this->dueDate?->format('Y-m-d'),
            'discount_rate' => $this->discountRate,
            'discount_days' => $this->discountDays,
            'discount_date' => $this->discountDate?->format('Y-m-d'),
            'title' => $this->title,
            'label' => $this->label,
            'intro' => $this->intro,
            'note' => $this->note,
            'reduction' => $this->reduction,
            'currency_code' => $this->currencyCode,
            'net_gross' => $this->netGross?->value,
            'quote' => $this->quote,
            'payment_types' => $this->paymentTypes,
            'invoice_id' => $this->invoiceId,
            'offer_id' => $this->offerId,
            'confirmation_id' => $this->confirmationId,
            'recurring_id' => $this->recurringId,
            'free_text_id' => $this->freeTextId,
            'template_id' => $this->templateId,
        ];

        // Null-Felder entfernen
        $data = array_filter($data, static fn (mixed $v): bool => null !== $v);

        if ([] !== $this->items) {
            $data['invoice-items'] = [
                'invoice-item' => array_map(static fn (InvoiceItemCreateOptions $item): array => $item->toArray(), $this->items),
            ];
        }

        return $data;
    }
}
