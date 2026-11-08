<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\Money;

/**
 * Typisierter Payload für POST /reminder-texts gemäß Billomat-API.
 *
 * Doku:
 * https://www.billomat.com/api/einstellungen/mahnstufen/
 *
 * Generiert aus tools/codegen/definitions/reminder-texts.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final readonly class ReminderTextCreateOptions implements WriteOptions
{
    public function __construct(
        /**
         * Name der Mahnstufe.
         *
         * Billomat-Feld: name
         * Typ: ALNUM
         */
        public string $name,
        /**
         * Betreff.
         *
         * Billomat-Feld: subject
         * Typ: ALNUM
         */
        public ?string $subject = null,
        /**
         * Einleitung.
         *
         * Billomat-Feld: header
         * Typ: ALNUM
         */
        public ?string $header = null,
        /**
         * Anmerkung.
         *
         * Billomat-Feld: footer
         * Typ: ALNUM
         */
        public ?string $footer = null,
        /**
         * Reihenfolge der Mahnstufe.
         *
         * Billomat-Feld: sorting
         * Typ: INT
         * Default: Wird kein Wert übergeben, wird die Mahnstufe am Ende positioniert.
         */
        public ?int $sort = null,
        /**
         * Name der Mahngebühr (falls vorhanden).
         *
         * Billomat-Feld: charge_name
         * Typ: ALNUM
         */
        public ?string $chargeName = null,
        /**
         * Beschreibung der Mahngebühr (falls vorhanden).
         *
         * Billomat-Feld: charge_description
         * Typ: ALNUM
         */
        public ?string $chargeDescription = null,
        /**
         * Höhe der Mahngebühr (falls vorhanden).
         *
         * Billomat-Feld: charge_amount
         * Typ: FLOAT
         */
        public ?Money $chargeAmount = null,
    ) {
    }

    /** Kopie mit geändertem `name`. */
    public function withName(string $name): self
    {
        return clone($this, ['name' => $name]);
    }

    /** Kopie mit geändertem `subject`. */
    public function withSubject(?string $subject): self
    {
        return clone($this, ['subject' => $subject]);
    }

    /** Kopie mit geändertem `header`. */
    public function withHeader(?string $header): self
    {
        return clone($this, ['header' => $header]);
    }

    /** Kopie mit geändertem `footer`. */
    public function withFooter(?string $footer): self
    {
        return clone($this, ['footer' => $footer]);
    }

    /** Kopie mit geändertem `sort`. */
    public function withSort(?int $sort): self
    {
        return clone($this, ['sort' => $sort]);
    }

    /** Kopie mit geändertem `chargeName`. */
    public function withChargeName(?string $chargeName): self
    {
        return clone($this, ['chargeName' => $chargeName]);
    }

    /** Kopie mit geändertem `chargeDescription`. */
    public function withChargeDescription(?string $chargeDescription): self
    {
        return clone($this, ['chargeDescription' => $chargeDescription]);
    }

    /** Kopie mit geändertem `chargeAmount`. */
    public function withChargeAmount(?Money $chargeAmount): self
    {
        return clone($this, ['chargeAmount' => $chargeAmount]);
    }

    /**
     * Serialisiert die Options in ein Billomat-Payload-Array.
     *
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'subject' => $this->subject,
            'header' => $this->header,
            'footer' => $this->footer,
            'sorting' => $this->sort,
            'charge_name' => $this->chargeName,
            'charge_description' => $this->chargeDescription,
            'charge_amount' => $this->chargeAmount?->toString(),
        ], static fn (mixed $v): bool => null !== $v);
    }
}
