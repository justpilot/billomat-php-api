<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use DateTimeImmutable;
use Justpilot\Billomat\Model\Enum\NetGross;

/**
 * Typisierter Payload für PUT /delivery-notes/{id}.
 *
 * Nur gesetzte (nicht-null) Felder werden serialisiert (Partial Update).
 * Nur Lieferscheine im Status DRAFT sind voll editierbar.
 *
 * Doku:
 * https://www.billomat.com/en/api/delivery-notes/
 *
 * Generiert aus tools/codegen/definitions/delivery-notes.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final class DeliveryNoteUpdateOptions implements WriteOptions
{
    /**
     * ID des Kunden.
     *
     * Billomat-Feld: client_id
     * Typ: INT
     */
    public ?int $clientId = null;

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
     */
    public ?int $number = null;

    /**
     * Präfix der Lieferscheinnummer.
     *
     * Billomat-Feld: number_pre
     * Typ: ALNUM
     */
    public ?string $numberPre = null;

    /**
     * Mindestlänge der Lieferscheinnummer (wird mit führenden Nullen aufgefüllt).
     *
     * Billomat-Feld: number_length
     * Typ: INT
     */
    public ?int $numberLength = null;

    /**
     * Lieferscheindatum.
     *
     * Format: YYYY-MM-DD
     *
     * Billomat-Feld: date
     * Typ: DATE
     */
    public ?DateTimeImmutable $date = null;

    /**
     * Komplette Lieferscheinadresse.
     *
     * Laut Doku veraltet – Billomat verarbeitet das Feld nicht mehr, bitte contact_id oder client_id verwenden.
     *
     * Billomat-Feld: address
     * Typ: ALNUM
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
            'net_gross' => $this->netGross?->value,
            'currency_code' => $this->currencyCode,
            'quote' => $this->quote,
            'invoice_id' => $this->invoiceId,
            'offer_id' => $this->offerId,
            'confirmation_id' => $this->confirmationId,
            'free_text_id' => $this->freeTextId,
            'template_id' => $this->templateId,
        ];

        // Null-Felder entfernen
        return array_filter($data, static fn (mixed $v): bool => null !== $v);
    }
}
