<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

/**
 * Typisierter Payload für PUT /clients/{id} gemäß Billomat-API.
 *
 * Nur gesetzte (nicht-null) Felder werden serialisiert.
 * Doku: https://www.billomat.com/api/kunden/  (Abschnitt: Kunde bearbeiten)
 */
final class ClientUpdateOptions
{
    /** Archivierungsstatus (1=archiviert, 0=nicht archiviert) */
    public ?bool $archived = null;

    /** Firmenname */
    public ?string $name = null;

    /** Straße */
    public ?string $street = null;

    /** PLZ */
    public ?string $zip = null;

    /** Ort */
    public ?string $city = null;

    /** Bundesland / Region */
    public ?string $state = null;

    /** Ländercode nach ISO 3166 Alpha-2 */
    public ?string $countryCode = null;

    /** Vorname Ansprechpartner */
    public ?string $firstName = null;

    /** Nachname Ansprechpartner */
    public ?string $lastName = null;

    /** Anrede */
    public ?string $salutation = null;

    /** Telefon */
    public ?string $phone = null;

    /** Fax */
    public ?string $fax = null;

    /** Mobiltelefon */
    public ?string $mobile = null;

    /** E-Mail-Adresse */
    public ?string $email = null;

    /** Website (ohne http) */
    public ?string $www = null;

    /** Steuernummer */
    public ?string $taxNumber = null;

    /** USt-IdNr. */
    public ?string $vatNumber = null;

    /** Notiz */
    public ?string $note = null;

    /** Rabatt in Prozent */
    public ?float $reduction = null;

    /** Debitorennummer */
    public ?int $debitorAccountNumber = null;

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        $data = [
            'archived' => $this->archived === null ? null : ($this->archived ? 1 : 0),
            'name' => $this->name,
            'street' => $this->street,
            'zip' => $this->zip,
            'city' => $this->city,
            'state' => $this->state,
            'country_code' => $this->countryCode,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'salutation' => $this->salutation,
            'phone' => $this->phone,
            'fax' => $this->fax,
            'mobile' => $this->mobile,
            'email' => $this->email,
            'www' => $this->www,
            'tax_number' => $this->taxNumber,
            'vat_number' => $this->vatNumber,
            'note' => $this->note,
            'reduction' => $this->reduction,
            'debitor_account_number' => $this->debitorAccountNumber,
        ];

        return array_filter($data, static fn($v) => $v !== null);
    }
}