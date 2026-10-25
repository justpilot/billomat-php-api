<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

/**
 * Typisierter Payload für POST /contacts gemäß Billomat-API.
 *
 * Null-Werte bedeuten "nicht gesetzt" und werden beim Serialisieren ignoriert.
 *
 * Doku:
 * https://www.billomat.com/en/api/clients/contacts/
 *
 * Generiert aus tools/codegen/definitions/contacts.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final readonly class ContactCreateOptions implements WriteOptions
{
    public function __construct(
        /**
         * ID des Kunden, zu dem der Kontakt gehört.
         *
         * Billomat-Feld: client_id
         * Typ: INT
         */
        public int $clientId,
        /**
         * Bezeichnung des Kontakts.
         *
         * Billomat-Feld: label
         * Typ: ALNUM
         */
        public ?string $label = null,
        /**
         * Anrede.
         *
         * Billomat-Feld: salutation
         * Typ: ALNUM
         */
        public ?string $salutation = null,
        /**
         * Vorname.
         *
         * Billomat-Feld: first_name
         * Typ: ALNUM
         */
        public ?string $firstName = null,
        /**
         * Nachname.
         *
         * Billomat-Feld: last_name
         * Typ: ALNUM
         */
        public ?string $lastName = null,
        /**
         * Straße.
         *
         * Billomat-Feld: street
         * Typ: ALNUM
         */
        public ?string $street = null,
        /**
         * Postleitzahl.
         *
         * Billomat-Feld: zip
         * Typ: ALNUM
         */
        public ?string $zip = null,
        /**
         * Ort.
         *
         * Billomat-Feld: city
         * Typ: ALNUM
         */
        public ?string $city = null,
        /**
         * Bundesland / Bezirk / Region.
         *
         * Billomat-Feld: state
         * Typ: ALNUM
         */
        public ?string $state = null,
        /**
         * Ländercode (ISO 3166-1 Alpha-2).
         *
         * Beispiel: "DE", "AT", "CH"
         *
         * Billomat-Feld: country_code
         * Typ: Ländercode nach ISO 3166 Alpha-2
         */
        public ?string $countryCode = null,
        /**
         * E-Mail-Adresse.
         *
         * Billomat-Feld: email
         * Typ: gültige E-Mail-Adresse
         */
        public ?string $email = null,
        /**
         * Telefonnummer.
         *
         * Billomat-Feld: phone
         * Typ: ALNUM
         */
        public ?string $phone = null,
        /**
         * Faxnummer.
         *
         * Billomat-Feld: fax
         * Typ: ALNUM
         */
        public ?string $fax = null,
        /**
         * Mobilnummer.
         *
         * Billomat-Feld: mobile
         * Typ: ALNUM
         */
        public ?string $mobile = null,
        /**
         * Notiz zum Kontakt.
         *
         * Billomat-Feld: note
         */
        public ?string $note = null,
        /**
         * Firmenname.
         *
         * Billomat-Feld: name
         * Typ: ALNUM
         */
        public ?string $name = null,
        /**
         * Website (ohne Schema).
         *
         * Beispiel: "example.com"
         *
         * Billomat-Feld: www
         * Typ: URL (ohne http)
         */
        public ?string $www = null,
    ) {
    }

    /** Kopie mit geändertem `clientId`. */
    public function withClientId(int $clientId): self
    {
        return clone($this, ['clientId' => $clientId]);
    }

    /** Kopie mit geändertem `label`. */
    public function withLabel(?string $label): self
    {
        return clone($this, ['label' => $label]);
    }

    /** Kopie mit geändertem `salutation`. */
    public function withSalutation(?string $salutation): self
    {
        return clone($this, ['salutation' => $salutation]);
    }

    /** Kopie mit geändertem `firstName`. */
    public function withFirstName(?string $firstName): self
    {
        return clone($this, ['firstName' => $firstName]);
    }

    /** Kopie mit geändertem `lastName`. */
    public function withLastName(?string $lastName): self
    {
        return clone($this, ['lastName' => $lastName]);
    }

    /** Kopie mit geändertem `street`. */
    public function withStreet(?string $street): self
    {
        return clone($this, ['street' => $street]);
    }

    /** Kopie mit geändertem `zip`. */
    public function withZip(?string $zip): self
    {
        return clone($this, ['zip' => $zip]);
    }

    /** Kopie mit geändertem `city`. */
    public function withCity(?string $city): self
    {
        return clone($this, ['city' => $city]);
    }

    /** Kopie mit geändertem `state`. */
    public function withState(?string $state): self
    {
        return clone($this, ['state' => $state]);
    }

    /** Kopie mit geändertem `countryCode`. */
    public function withCountryCode(?string $countryCode): self
    {
        return clone($this, ['countryCode' => $countryCode]);
    }

    /** Kopie mit geändertem `email`. */
    public function withEmail(?string $email): self
    {
        return clone($this, ['email' => $email]);
    }

    /** Kopie mit geändertem `phone`. */
    public function withPhone(?string $phone): self
    {
        return clone($this, ['phone' => $phone]);
    }

    /** Kopie mit geändertem `fax`. */
    public function withFax(?string $fax): self
    {
        return clone($this, ['fax' => $fax]);
    }

    /** Kopie mit geändertem `mobile`. */
    public function withMobile(?string $mobile): self
    {
        return clone($this, ['mobile' => $mobile]);
    }

    /** Kopie mit geändertem `note`. */
    public function withNote(?string $note): self
    {
        return clone($this, ['note' => $note]);
    }

    /** Kopie mit geändertem `name`. */
    public function withName(?string $name): self
    {
        return clone($this, ['name' => $name]);
    }

    /** Kopie mit geändertem `www`. */
    public function withWww(?string $www): self
    {
        return clone($this, ['www' => $www]);
    }

    /**
     * Serialisiert die Options in ein Billomat-Payload-Array.
     *
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'client_id' => $this->clientId,
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
        ], static fn (mixed $v): bool => null !== $v);
    }
}
