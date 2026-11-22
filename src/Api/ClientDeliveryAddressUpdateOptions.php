<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

/**
 * Typisierter Payload für PUT /client-delivery-addresses/{id} gemäß Billomat-API.
 *
 * Nur gesetzte (nicht-null) Felder werden serialisiert (Partial Update).
 * Der Kunde einer Lieferadresse lässt sich nicht ändern.
 *
 * Doku:
 * https://www.billomat.com/api/lieferadressen/
 *
 * Generiert aus tools/codegen/definitions/client-delivery-addresses.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 *
 * @experimental Billomat stellt die Lieferadressen noch fertig; laut Doku gelten die Endpunkte erst ab dem 01.01.2027 verbindlich. Bis dahin kann sich die Klasse auch in einem Minor-Release ändern (siehe docs/concepts/backward-compatibility.md).
 */
final readonly class ClientDeliveryAddressUpdateOptions implements WriteOptions
{
    public function __construct(
        /**
         * Bezeichnung der Lieferadresse.
         *
         * Beispiel: "Lager Berlin"
         *
         * Billomat-Feld: label
         */
        public string|Clear|null $label = null,
        /**
         * Firmenname.
         *
         * Billomat-Feld: name
         * Typ: ALNUM
         */
        public string|Clear|null $name = null,
        /**
         * Anrede.
         *
         * Billomat-Feld: salutation
         * Typ: ALNUM
         */
        public string|Clear|null $salutation = null,
        /**
         * Vorname.
         *
         * Billomat-Feld: first_name
         * Typ: ALNUM
         */
        public string|Clear|null $firstName = null,
        /**
         * Nachname.
         *
         * Billomat-Feld: last_name
         * Typ: ALNUM
         */
        public string|Clear|null $lastName = null,
        /**
         * Straße.
         *
         * Billomat-Feld: street
         * Typ: ALNUM
         */
        public string|Clear|null $street = null,
        /**
         * Adresszusatz (Zeile 2).
         *
         * Billomat-Feld: address_line_2
         */
        public string|Clear|null $addressLine2 = null,
        /**
         * Adresszusatz (Zeile 3).
         *
         * Billomat-Feld: address_line_3
         */
        public string|Clear|null $addressLine3 = null,
        /**
         * Postleitzahl.
         *
         * Billomat-Feld: zip
         * Typ: ALNUM
         */
        public string|Clear|null $zip = null,
        /**
         * Ort.
         *
         * Billomat-Feld: city
         * Typ: ALNUM
         */
        public string|Clear|null $city = null,
        /**
         * Bundesland / Bezirk / Region.
         *
         * Billomat-Feld: state
         * Typ: ALNUM
         */
        public string|Clear|null $state = null,
        /**
         * Ländercode (ISO 3166-1 Alpha-2).
         *
         * Beispiel: "DE", "AT", "CH"
         *
         * Billomat-Feld: country_code
         * Typ: Ländercode nach ISO 3166 Alpha-2
         */
        public string|Clear|null $countryCode = null,
        /**
         * Standard-Lieferadresse des Kunden?
         *
         * Billomat-Feld: is_default
         */
        public bool|Clear|null $isDefault = null,
    ) {
    }

    /** Kopie mit geändertem `label`. */
    public function withLabel(string|Clear|null $label): self
    {
        return clone($this, ['label' => $label]);
    }

    /** Kopie mit geändertem `name`. */
    public function withName(string|Clear|null $name): self
    {
        return clone($this, ['name' => $name]);
    }

    /** Kopie mit geändertem `salutation`. */
    public function withSalutation(string|Clear|null $salutation): self
    {
        return clone($this, ['salutation' => $salutation]);
    }

    /** Kopie mit geändertem `firstName`. */
    public function withFirstName(string|Clear|null $firstName): self
    {
        return clone($this, ['firstName' => $firstName]);
    }

    /** Kopie mit geändertem `lastName`. */
    public function withLastName(string|Clear|null $lastName): self
    {
        return clone($this, ['lastName' => $lastName]);
    }

    /** Kopie mit geändertem `street`. */
    public function withStreet(string|Clear|null $street): self
    {
        return clone($this, ['street' => $street]);
    }

    /** Kopie mit geändertem `addressLine2`. */
    public function withAddressLine2(string|Clear|null $addressLine2): self
    {
        return clone($this, ['addressLine2' => $addressLine2]);
    }

    /** Kopie mit geändertem `addressLine3`. */
    public function withAddressLine3(string|Clear|null $addressLine3): self
    {
        return clone($this, ['addressLine3' => $addressLine3]);
    }

    /** Kopie mit geändertem `zip`. */
    public function withZip(string|Clear|null $zip): self
    {
        return clone($this, ['zip' => $zip]);
    }

    /** Kopie mit geändertem `city`. */
    public function withCity(string|Clear|null $city): self
    {
        return clone($this, ['city' => $city]);
    }

    /** Kopie mit geändertem `state`. */
    public function withState(string|Clear|null $state): self
    {
        return clone($this, ['state' => $state]);
    }

    /** Kopie mit geändertem `countryCode`. */
    public function withCountryCode(string|Clear|null $countryCode): self
    {
        return clone($this, ['countryCode' => $countryCode]);
    }

    /** Kopie mit geändertem `isDefault`. */
    public function withIsDefault(bool|Clear|null $isDefault): self
    {
        return clone($this, ['isDefault' => $isDefault]);
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
                'label' => $this->label,
                'name' => $this->name,
                'salutation' => $this->salutation,
                'first_name' => $this->firstName,
                'last_name' => $this->lastName,
                'street' => $this->street,
                'address_line_2' => $this->addressLine2,
                'address_line_3' => $this->addressLine3,
                'zip' => $this->zip,
                'city' => $this->city,
                'state' => $this->state,
                'country_code' => $this->countryCode,
                'is_default' => \is_bool($this->isDefault) ? ($this->isDefault ? 1 : 0) : $this->isDefault,
            ], static fn (mixed $v): bool => null !== $v),
        );
    }
}
