<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use DateTimeImmutable;
use Justpilot\Billomat\Model\Enum\NetGross;

/**
 * Typisierter Payload für POST /offers.
 *
 * Null-Werte bedeuten "nicht gesetzt" und werden beim Serialisieren ignoriert.
 * Positionen lassen sich beim Anlegen über addItem() direkt mitsenden.
 *
 * Doku:
 * https://www.billomat.com/en/api/estimates/
 *
 * Generiert aus tools/codegen/definitions/offers.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final class OfferCreateOptions
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
     * Präfix der Angebotsnummer.
     *
     * Billomat-Feld: number_pre
     * Typ: ALNUM
     * Default: Wert aus Einstellungen
     */
    public ?string $numberPre = null;

    /**
     * Mindestlänge der Angebotsnummer (wird mit führenden Nullen aufgefüllt).
     *
     * Billomat-Feld: number_length
     * Typ: INT
     * Default: Wert aus Einstellungen
     */
    public ?int $numberLength = null;

    /**
     * Angebotsdatum.
     *
     * Format: YYYY-MM-DD
     *
     * Billomat-Feld: date
     * Typ: DATE
     * Default: heute
     */
    public ?DateTimeImmutable $date = null;

    /**
     * Gültigkeit in Tagen (Angebotsgültigkeit).
     *
     * Billomat-Feld: validity_days
     * Typ: INT
     * Default: Gültigkeitstage aus Einstellungen
     */
    public ?int $validityDays = null;

    /**
     * Vollständige Angebotsadresse (formatiert).
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
     */
    public ?int $discountRate = null;

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
     * Währung (ISO-Währungscode).
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
     * Default: ID der Standardvorlage
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
     * Default: date + validity_days
     */
    public ?DateTimeImmutable $validityDate = null;

    /**
     * @var list<OfferItemCreateOptions>
     */
    private array $items = [];

    /**
     * Fügt eine Position hinzu, die beim Anlegen mitgesendet wird.
     */
    public function addItem(OfferItemCreateOptions $item): self
    {
        $this->items[] = $item;

        return $this;
    }

    /**
     * @return list<OfferItemCreateOptions>
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
            'currency_code' => $this->currencyCode,
            'net_gross' => $this->netGross?->value,
            'quote' => $this->quote,
            'free_text_id' => $this->freeTextId,
            'template_id' => $this->templateId,
        ];

        // Null-Felder entfernen
        $data = array_filter($data, static fn (mixed $v): bool => null !== $v);

        if ([] !== $this->items) {
            $data['offer-items'] = [
                'offer-item' => array_map(static fn (OfferItemCreateOptions $item): array => $item->toArray(), $this->items),
            ];
        }

        return $data;
    }
}
