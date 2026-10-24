<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use DateTimeImmutable;
use Justpilot\Billomat\Model\Enum\NetGross;

/**
 * Typisierter Payload für POST /delivery-notes.
 *
 * Null-Werte bedeuten "nicht gesetzt" und werden beim Serialisieren ignoriert.
 * Positionen lassen sich beim Anlegen über addItem() direkt mitsenden.
 *
 * Doku:
 * https://www.billomat.com/en/api/delivery-notes/
 *
 * Generiert aus tools/codegen/definitions/delivery-notes.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final class DeliveryNoteCreateOptions
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
     * Präfix der Lieferscheinnummer.
     *
     * Billomat-Feld: number_pre
     * Typ: ALNUM
     * Default: Wert aus Einstellungen
     */
    public ?string $numberPre = null;

    /**
     * Mindestlänge der Lieferscheinnummer (wird mit führenden Nullen aufgefüllt).
     *
     * Billomat-Feld: number_length
     * Typ: INT
     * Default: Wert aus den Einstellungen
     */
    public ?int $numberLength = null;

    /**
     * Lieferscheindatum.
     *
     * Format: YYYY-MM-DD
     *
     * Billomat-Feld: date
     * Typ: DATE
     * Default: heute
     */
    public ?DateTimeImmutable $date = null;

    /**
     * Komplette Lieferscheinadresse.
     *
     * Laut Doku veraltet – Billomat verarbeitet das Feld nicht mehr, bitte contact_id oder client_id verwenden.
     *
     * Billomat-Feld: address
     * Typ: ALNUM
     * Default: Adresse des Kunden
     */
    public ?string $address = null;

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
     * Preisbasis (Brutto- oder Netto-Preise).
     *
     * Billomat-Feld: net_gross
     */
    public ?NetGross $netGross = null;

    /**
     * Rabatt (absolut oder als Prozentwert, z. B. "10" oder "10%").
     *
     * Billomat-Feld: reduction
     */
    public ?string $reduction = null;

    /**
     * Währung (ISO-Währungscode).
     *
     * Billomat-Feld: currency_code
     */
    public ?string $currencyCode = null;

    /**
     * Währungskurs (für Umrechnung in die Standard-Währung).
     *
     * Billomat-Feld: quote
     */
    public ?float $quote = null;

    /**
     * ID der Quell-Rechnung.
     *
     * Billomat-Feld: invoice_id
     * Typ: INT
     */
    public ?int $invoiceId = null;

    /**
     * ID der Quell-Auftragsbestätigung.
     *
     * Billomat-Feld: confirmation_id
     * Typ: INT
     */
    public ?int $confirmationId = null;

    /**
     * ID des Freitextes zur Belegung von title, label, intro und note.
     *
     * Billomat-Feld: free_text_id
     * Typ: INT
     */
    public ?int $freeTextId = null;

    /**
     * ID der Vorlage, mit der der Lieferschein abgeschlossen wird.
     *
     * Billomat-Feld: template_id
     * Typ: INT
     * Default: ID der Standardvorlage
     */
    public ?int $templateId = null;

    /**
     * ID des Quell-Angebots.
     *
     * Billomat-Feld: offer_id
     * Typ: INT
     */
    public ?int $offerId = null;

    /**
     * @var list<DeliveryNoteItemCreateOptions>
     */
    private array $items = [];

    /**
     * Fügt eine Position hinzu, die beim Anlegen mitgesendet wird.
     */
    public function addItem(DeliveryNoteItemCreateOptions $item): self
    {
        $this->items[] = $item;

        return $this;
    }

    /**
     * @return list<DeliveryNoteItemCreateOptions>
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
            'title' => $this->title,
            'label' => $this->label,
            'intro' => $this->intro,
            'note' => $this->note,
            'reduction' => $this->reduction,
            'currency_code' => $this->currencyCode,
            'net_gross' => $this->netGross?->value,
            'quote' => $this->quote,
            'invoice_id' => $this->invoiceId,
            'offer_id' => $this->offerId,
            'confirmation_id' => $this->confirmationId,
            'free_text_id' => $this->freeTextId,
            'template_id' => $this->templateId,
        ];

        // Null-Felder entfernen
        $data = array_filter($data, static fn (mixed $v): bool => null !== $v);

        if ([] !== $this->items) {
            $data['delivery-note-items'] = [
                'delivery-note-item' => array_map(static fn (DeliveryNoteItemCreateOptions $item): array => $item->toArray(), $this->items),
            ];
        }

        return $data;
    }
}
