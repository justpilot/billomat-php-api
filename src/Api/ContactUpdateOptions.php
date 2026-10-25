<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

/**
 * Typisierter Payload für PUT /contacts/{id} gemäß Billomat-API.
 *
 * Nur gesetzte (nicht-null) Felder werden serialisiert (Partial Update).
 * Der Kunde eines Kontakts lässt sich nicht ändern.
 *
 * Doku:
 * https://www.billomat.com/en/api/clients/contacts/
 *
 * Generiert aus tools/codegen/definitions/contacts.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final readonly class ContactUpdateOptions implements WriteOptions
{
    public function __construct(
        /**
         * Bezeichnung des Kontakts.
         *
         * Billomat-Feld: label
         * Typ: ALNUM
         */
        public string|Clear|null $label = null,
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
         * E-Mail-Adresse.
         *
         * Billomat-Feld: email
         * Typ: gültige E-Mail-Adresse
         */
        public string|Clear|null $email = null,
        /**
         * Telefonnummer.
         *
         * Billomat-Feld: phone
         * Typ: ALNUM
         */
        public string|Clear|null $phone = null,
        /**
         * Faxnummer.
         *
         * Billomat-Feld: fax
         * Typ: ALNUM
         */
        public string|Clear|null $fax = null,
        /**
         * Mobilnummer.
         *
         * Billomat-Feld: mobile
         * Typ: ALNUM
         */
        public string|Clear|null $mobile = null,
        /**
         * Notiz zum Kontakt.
         *
         * Billomat-Feld: note
         */
        public string|Clear|null $note = null,
        /**
         * Firmenname.
         *
         * Billomat-Feld: name
         * Typ: ALNUM
         */
        public string|Clear|null $name = null,
        /**
         * Website (ohne Schema).
         *
         * Beispiel: "example.com"
         *
         * Billomat-Feld: www
         * Typ: URL (ohne http)
         */
        public string|Clear|null $www = null,
    ) {
    }

    /** Kopie mit geändertem `label`. */
    public function withLabel(string|Clear|null $label): self
    {
        return clone($this, ['label' => $label]);
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

    /** Kopie mit geändertem `email`. */
    public function withEmail(string|Clear|null $email): self
    {
        return clone($this, ['email' => $email]);
    }

    /** Kopie mit geändertem `phone`. */
    public function withPhone(string|Clear|null $phone): self
    {
        return clone($this, ['phone' => $phone]);
    }

    /** Kopie mit geändertem `fax`. */
    public function withFax(string|Clear|null $fax): self
    {
        return clone($this, ['fax' => $fax]);
    }

    /** Kopie mit geändertem `mobile`. */
    public function withMobile(string|Clear|null $mobile): self
    {
        return clone($this, ['mobile' => $mobile]);
    }

    /** Kopie mit geändertem `note`. */
    public function withNote(string|Clear|null $note): self
    {
        return clone($this, ['note' => $note]);
    }

    /** Kopie mit geändertem `name`. */
    public function withName(string|Clear|null $name): self
    {
        return clone($this, ['name' => $name]);
    }

    /** Kopie mit geändertem `www`. */
    public function withWww(string|Clear|null $www): self
    {
        return clone($this, ['www' => $www]);
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
                'salutation' => $this->salutation,
                'first_name' => $this->firstName,
                'last_name' => $this->lastName,
                'street' => $this->street,
                'zip' => $this->zip,
                'city' => $this->city,
                'state' => $this->state,
                'country_code' => $this->countryCode,
                'email' => $this->email,
                'phone' => $this->phone,
                'fax' => $this->fax,
                'mobile' => $this->mobile,
                'note' => $this->note,
                'name' => $this->name,
                'www' => $this->www,
            ], static fn (mixed $v): bool => null !== $v),
        );
    }
}
