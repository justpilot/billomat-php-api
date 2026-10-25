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
final class ContactUpdateOptions implements WriteOptions
{
    /**
     * Bezeichnung des Kontakts.
     *
     * Billomat-Feld: label
     * Typ: ALNUM
     */
    public ?string $label = null;

    /**
     * Anrede.
     *
     * Billomat-Feld: salutation
     * Typ: ALNUM
     */
    public ?string $salutation = null;

    /**
     * Vorname.
     *
     * Billomat-Feld: first_name
     * Typ: ALNUM
     */
    public ?string $firstName = null;

    /**
     * Nachname.
     *
     * Billomat-Feld: last_name
     * Typ: ALNUM
     */
    public ?string $lastName = null;

    /**
     * Straße.
     *
     * Billomat-Feld: street
     * Typ: ALNUM
     */
    public ?string $street = null;

    /**
     * Postleitzahl.
     *
     * Billomat-Feld: zip
     * Typ: ALNUM
     */
    public ?string $zip = null;

    /**
     * Ort.
     *
     * Billomat-Feld: city
     * Typ: ALNUM
     */
    public ?string $city = null;

    /**
     * Bundesland / Bezirk / Region.
     *
     * Billomat-Feld: state
     * Typ: ALNUM
     */
    public ?string $state = null;

    /**
     * Ländercode (ISO 3166-1 Alpha-2).
     *
     * Beispiel: "DE", "AT", "CH"
     *
     * Billomat-Feld: country_code
     * Typ: Ländercode nach ISO 3166 Alpha-2
     */
    public ?string $countryCode = null;

    /**
     * E-Mail-Adresse.
     *
     * Billomat-Feld: email
     * Typ: gültige E-Mail-Adresse
     */
    public ?string $email = null;

    /**
     * Telefonnummer.
     *
     * Billomat-Feld: phone
     * Typ: ALNUM
     */
    public ?string $phone = null;

    /**
     * Faxnummer.
     *
     * Billomat-Feld: fax
     * Typ: ALNUM
     */
    public ?string $fax = null;

    /**
     * Mobilnummer.
     *
     * Billomat-Feld: mobile
     * Typ: ALNUM
     */
    public ?string $mobile = null;

    /**
     * Notiz zum Kontakt.
     *
     * Billomat-Feld: note
     */
    public ?string $note = null;

    /**
     * Firmenname.
     *
     * Billomat-Feld: name
     * Typ: ALNUM
     */
    public ?string $name = null;

    /**
     * Website (ohne Schema).
     *
     * Beispiel: "example.com"
     *
     * Billomat-Feld: www
     * Typ: URL (ohne http)
     */
    public ?string $www = null;

    /**
     * Serialisiert die Options in ein Billomat-Payload-Array.
     *
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        $data = [
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
        ];

        // Null-Felder entfernen
        return array_filter($data, static fn (mixed $v): bool => null !== $v);
    }
}
