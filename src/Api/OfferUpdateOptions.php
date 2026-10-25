<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use DateTimeImmutable;
use Justpilot\Billomat\Model\Enum\NetGross;

/**
 * Typisierter Payload für PUT /offers/{id}.
 *
 * Nur gesetzte (nicht-null) Felder werden serialisiert (Partial Update).
 * Nur Angebote im Status DRAFT sind voll editierbar; Positionen und
 * Kommentare werden über die Sub-Ressourcen verwaltet.
 *
 * Doku:
 * https://www.billomat.com/en/api/estimates/
 *
 * Generiert aus tools/codegen/definitions/offers.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final class OfferUpdateOptions implements WriteOptions
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
     * Präfix der Angebotsnummer.
     *
     * Billomat-Feld: number_pre
     * Typ: ALNUM
     */
    public ?string $numberPre = null;

    /**
     * Mindestlänge der Angebotsnummer (wird mit führenden Nullen aufgefüllt).
     *
     * Billomat-Feld: number_length
     * Typ: INT
     */
    public ?int $numberLength = null;

    /**
     * Angebotsdatum.
     *
     * Format: YYYY-MM-DD
     *
     * Billomat-Feld: date
     * Typ: DATE
     */
    public ?DateTimeImmutable $date = null;

    /**
     * Gültigkeit in Tagen (Angebotsgültigkeit).
     *
     * Billomat-Feld: validity_days
     * Typ: INT
     */
    public ?int $validityDays = null;

    /**
     * Vollständige Angebotsadresse (formatiert).
     *
     * Billomat-Feld: address
     * Typ: ALNUM
     */
    public ?string $address = null;

    /**
     * Skonto in Prozent.
     *
     * Billomat-Feld: discount_rate
     */
    public ?float $discountRate = null;

    /**
     * Skontodatum.
     *
     * Format: YYYY-MM-DD
     *
     * Billomat-Feld: discount_date
     */
    public ?DateTimeImmutable $discountDate = null;

    /**
     * Skontozeitraum in Tagen.
     *
     * Billomat-Feld: discount_days
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
     * Typ: ALNUM („NET“, „GROSS“)
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
     * Währung (ISO-Währungscode).
     *
     * Billomat-Feld: currency_code
     * Typ: ISO-Währungscode
     */
    public ?string $currencyCode = null;

    /**
     * Währungskurs (für Umrechnung in die Standard-Währung).
     *
     * Billomat-Feld: quote
     * Typ: FLOAT
     */
    public ?float $quote = null;

    /**
     * ID des Freitextes zur Belegung von title, label, intro und note.
     *
     * Billomat-Feld: free_text_id
     * Typ: INT
     */
    public ?int $freeTextId = null;

    /**
     * ID der Vorlage, mit der das Angebot abgeschlossen wird.
     *
     * Billomat-Feld: template_id
     * Typ: INT
     */
    public ?int $templateId = null;

    /**
     * Konkretes Ablaufdatum des Angebots.
     *
     * Wird übersteuert durch `validityDays`, wenn beide gesetzt sind.
     * Format: YYYY-MM-DD
     *
     * Billomat-Feld: validity_date
     * Typ: DATE
     */
    public ?DateTimeImmutable $validityDate = null;

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
            'validity_days' => $this->validityDays,
            'validity_date' => $this->validityDate?->format('Y-m-d'),
            'discount_rate' => $this->discountRate,
            'discount_days' => $this->discountDays,
            'discount_date' => $this->discountDate?->format('Y-m-d'),
            'title' => $this->title,
            'label' => $this->label,
            'intro' => $this->intro,
            'note' => $this->note,
            'reduction' => $this->reduction,
            'net_gross' => $this->netGross?->value,
            'currency_code' => $this->currencyCode,
            'quote' => $this->quote,
            'free_text_id' => $this->freeTextId,
            'template_id' => $this->templateId,
        ];

        // Null-Felder entfernen
        return array_filter($data, static fn (mixed $v): bool => null !== $v);
    }
}
