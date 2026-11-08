<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\Money;

/**
 * Typisierter Payload für PUT /reminder-texts/{id} gemäß Billomat-API.
 *
 * Nur gesetzte (nicht-null) Felder werden serialisiert (Partial Update).
 *
 * Doku:
 * https://www.billomat.com/api/einstellungen/mahnstufen/
 *
 * Generiert aus tools/codegen/definitions/reminder-texts.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final readonly class ReminderTextUpdateOptions implements WriteOptions
{
    public function __construct(
        /**
         * Name der Mahnstufe.
         *
         * Billomat-Feld: name
         * Typ: ALNUM
         */
        public string|Clear|null $name = null,
        /**
         * Betreff.
         *
         * Billomat-Feld: subject
         * Typ: ALNUM
         */
        public string|Clear|null $subject = null,
        /**
         * Einleitung.
         *
         * Billomat-Feld: header
         * Typ: ALNUM
         */
        public string|Clear|null $header = null,
        /**
         * Anmerkung.
         *
         * Billomat-Feld: footer
         * Typ: ALNUM
         */
        public string|Clear|null $footer = null,
        /**
         * Reihenfolge der Mahnstufe.
         *
         * Billomat-Feld: sorting
         * Typ: INT
         */
        public int|Clear|null $sort = null,
        /**
         * Name der Mahngebühr (falls vorhanden).
         *
         * Billomat-Feld: charge_name
         * Typ: ALNUM
         */
        public string|Clear|null $chargeName = null,
        /**
         * Beschreibung der Mahngebühr (falls vorhanden).
         *
         * Billomat-Feld: charge_description
         * Typ: ALNUM
         */
        public string|Clear|null $chargeDescription = null,
        /**
         * Höhe der Mahngebühr (falls vorhanden).
         *
         * Billomat-Feld: charge_amount
         * Typ: FLOAT
         */
        public Money|Clear|null $chargeAmount = null,
    ) {
    }

    /** Kopie mit geändertem `name`. */
    public function withName(string|Clear|null $name): self
    {
        return clone($this, ['name' => $name]);
    }

    /** Kopie mit geändertem `subject`. */
    public function withSubject(string|Clear|null $subject): self
    {
        return clone($this, ['subject' => $subject]);
    }

    /** Kopie mit geändertem `header`. */
    public function withHeader(string|Clear|null $header): self
    {
        return clone($this, ['header' => $header]);
    }

    /** Kopie mit geändertem `footer`. */
    public function withFooter(string|Clear|null $footer): self
    {
        return clone($this, ['footer' => $footer]);
    }

    /** Kopie mit geändertem `sort`. */
    public function withSort(int|Clear|null $sort): self
    {
        return clone($this, ['sort' => $sort]);
    }

    /** Kopie mit geändertem `chargeName`. */
    public function withChargeName(string|Clear|null $chargeName): self
    {
        return clone($this, ['chargeName' => $chargeName]);
    }

    /** Kopie mit geändertem `chargeDescription`. */
    public function withChargeDescription(string|Clear|null $chargeDescription): self
    {
        return clone($this, ['chargeDescription' => $chargeDescription]);
    }

    /** Kopie mit geändertem `chargeAmount`. */
    public function withChargeAmount(Money|Clear|null $chargeAmount): self
    {
        return clone($this, ['chargeAmount' => $chargeAmount]);
    }

    /**
     * Serialisiert die Options in ein Billomat-Payload-Array.
     *
     * Nicht gesetzte Felder (null) fehlen im Payload, {@see Clear::Field} wird als null gesendet und leert das Feld.
     *
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return array_map(
            static fn (mixed $v): mixed => $v instanceof Clear ? null : $v,
            array_filter([
                'name' => $this->name,
                'subject' => $this->subject,
                'header' => $this->header,
                'footer' => $this->footer,
                'sorting' => $this->sort,
                'charge_name' => $this->chargeName,
                'charge_description' => $this->chargeDescription,
                'charge_amount' => $this->chargeAmount instanceof Money ? $this->chargeAmount->toString() : $this->chargeAmount,
            ], static fn (mixed $v): bool => null !== $v),
        );
    }
}
