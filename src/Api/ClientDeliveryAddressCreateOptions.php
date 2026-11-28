<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

/**
 * Typisierter Payload für POST /client-delivery-addresses.
 *
 * Null-Werte bedeuten "nicht gesetzt" und werden beim Serialisieren ignoriert.
 *
 * Doku:
 * https://www.billomat.com/api/lieferadressen/
 *
 * Generiert aus tools/codegen/definitions/client-delivery-addresses.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final readonly class ClientDeliveryAddressCreateOptions implements WriteOptions
{
    public function __construct(
        /**
         * Bezeichnung der Lieferadresse.
         *
         * Von Billomat geliefert und angenommen, aber nicht dokumentiert.
         *
         * Billomat-Feld: label
         */
        public ?string $label = null,
        /**
         * Firmenname.
         *
         * Billomat-Feld: name
         * Typ: ALNUM
         */
        public ?string $name = null,
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
         * Zweite Adresszeile.
         *
         * Von Billomat geliefert und angenommen, aber nicht dokumentiert.
         *
         * Billomat-Feld: address_line_2
         */
        public ?string $addressLine2 = null,
        /**
         * Dritte Adresszeile.
         *
         * Von Billomat geliefert und angenommen, aber nicht dokumentiert.
         *
         * Billomat-Feld: address_line_3
         */
        public ?string $addressLine3 = null,
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
         * Ist das die Standard-Lieferadresse des Kunden?
         *
         * Es gibt höchstens eine pro Kunde: Wird eine Adresse zur Standardadresse, verliert die bisherige das Flag.
         * Von Billomat geliefert und angenommen, aber nicht dokumentiert.
         *
         * Billomat-Feld: is_default
         */
        public ?bool $isDefault = null,
    ) {
    }

    /** Kopie mit geändertem `label`. */
    public function withLabel(?string $label): self
    {
        return clone($this, ['label' => $label]);
    }

    /** Kopie mit geändertem `name`. */
    public function withName(?string $name): self
    {
        return clone($this, ['name' => $name]);
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

    /** Kopie mit geändertem `addressLine2`. */
    public function withAddressLine2(?string $addressLine2): self
    {
        return clone($this, ['addressLine2' => $addressLine2]);
    }

    /** Kopie mit geändertem `addressLine3`. */
    public function withAddressLine3(?string $addressLine3): self
    {
        return clone($this, ['addressLine3' => $addressLine3]);
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

    /** Kopie mit geändertem `isDefault`. */
    public function withIsDefault(?bool $isDefault): self
    {
        return clone($this, ['isDefault' => $isDefault]);
    }

    /**
     * Serialisiert die Options in ein Billomat-Payload-Array.
     *
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return array_filter([
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
            'is_default' => $this->isDefault,
        ], static fn (mixed $v): bool => null !== $v);
    }
}
