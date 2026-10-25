<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

/**
 * Typisierter Payload für POST /suppliers gemäß Billomat-API.
 *
 * Null-Werte bedeuten "nicht gesetzt" und werden beim Serialisieren ignoriert.
 *
 * Doku:
 * https://www.billomat.com/en/api/suppliers/
 *
 * Generiert aus tools/codegen/definitions/suppliers.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final readonly class SupplierCreateOptions implements WriteOptions
{
    public function __construct(
        /**
         * Firmenname des Lieferanten.
         *
         * Billomat-Feld: name
         * Typ: ALNUM
         */
        public string $name,
        /**
         * Eigene Kundennummer beim Lieferanten.
         *
         * Billomat-Feld: client_number
         * Typ: ALNUM
         */
        public ?string $clientNumber = null,
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
         * Default: Wert aus eigener Firma
         */
        public ?string $countryCode = null,
        /**
         * Notiz zum Lieferanten.
         *
         * Billomat-Feld: note
         * Typ: ALNUM
         */
        public ?string $note = null,
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
         * Website (ohne Schema).
         *
         * Beispiel: "example.com"
         *
         * Billomat-Feld: www
         * Typ: URL (ohne http)
         */
        public ?string $www = null,
        /**
         * Steuernummer.
         *
         * Billomat-Feld: tax_number
         * Typ: ALNUM
         */
        public ?string $taxNumber = null,
        /**
         * Umsatzsteuer-ID (USt-IdNr.).
         *
         * Billomat-Feld: vat_number
         * Typ: gültige Ust-IdNr.
         */
        public ?string $vatNumber = null,
        /**
         * Kontonummer.
         *
         * Billomat-Feld: bank_account_number
         * Typ: ALNUM
         */
        public ?string $bankAccountNumber = null,
        /**
         * Kontoinhaber.
         *
         * Billomat-Feld: bank_account_owner
         * Typ: ALNUM
         */
        public ?string $bankAccountOwner = null,
        /**
         * Bankleitzahl.
         *
         * Billomat-Feld: bank_number
         * Typ: ALNUM
         */
        public ?string $bankNumber = null,
        /**
         * Bankname.
         *
         * Billomat-Feld: bank_name
         * Typ: ALNUM
         */
        public ?string $bankName = null,
        /**
         * IBAN.
         *
         * Billomat-Feld: bank_iban
         * Typ: gültige IBAN
         */
        public ?string $bankIban = null,
        /**
         * SWIFT / BIC.
         *
         * Billomat liefert den BIC als `bank_swift`; `bank_bic` dient nur als Fallback.
         *
         * Billomat-Feld: bank_bic
         */
        public ?string $bankBic = null,
        /**
         * SWIFT / BIC laut Billomat-Doku.
         *
         * Alias zu `bankBic` – beide werden serialisiert.
         *
         * Billomat-Feld: bank_swift
         * Typ: ALNUM
         */
        public ?string $bankSwift = null,
        /**
         * Währung des Lieferanten (ISO-Währungscode).
         *
         * Leer = Account-Währung.
         *
         * Billomat-Feld: currency_code
         * Typ: ISO-Währungscode
         */
        public ?string $currencyCode = null,
        /**
         * SEPA-Gläubiger-ID.
         *
         * Billomat-Feld: creditor_identifier
         * Typ: gültige Gläubiger-ID
         */
        public ?string $creditorIdentifier = null,
    ) {
    }

    /** Kopie mit geändertem `name`. */
    public function withName(string $name): self
    {
        return clone($this, ['name' => $name]);
    }

    /** Kopie mit geändertem `clientNumber`. */
    public function withClientNumber(?string $clientNumber): self
    {
        return clone($this, ['clientNumber' => $clientNumber]);
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

    /** Kopie mit geändertem `note`. */
    public function withNote(?string $note): self
    {
        return clone($this, ['note' => $note]);
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

    /** Kopie mit geändertem `www`. */
    public function withWww(?string $www): self
    {
        return clone($this, ['www' => $www]);
    }

    /** Kopie mit geändertem `taxNumber`. */
    public function withTaxNumber(?string $taxNumber): self
    {
        return clone($this, ['taxNumber' => $taxNumber]);
    }

    /** Kopie mit geändertem `vatNumber`. */
    public function withVatNumber(?string $vatNumber): self
    {
        return clone($this, ['vatNumber' => $vatNumber]);
    }

    /** Kopie mit geändertem `bankAccountNumber`. */
    public function withBankAccountNumber(?string $bankAccountNumber): self
    {
        return clone($this, ['bankAccountNumber' => $bankAccountNumber]);
    }

    /** Kopie mit geändertem `bankAccountOwner`. */
    public function withBankAccountOwner(?string $bankAccountOwner): self
    {
        return clone($this, ['bankAccountOwner' => $bankAccountOwner]);
    }

    /** Kopie mit geändertem `bankNumber`. */
    public function withBankNumber(?string $bankNumber): self
    {
        return clone($this, ['bankNumber' => $bankNumber]);
    }

    /** Kopie mit geändertem `bankName`. */
    public function withBankName(?string $bankName): self
    {
        return clone($this, ['bankName' => $bankName]);
    }

    /** Kopie mit geändertem `bankIban`. */
    public function withBankIban(?string $bankIban): self
    {
        return clone($this, ['bankIban' => $bankIban]);
    }

    /** Kopie mit geändertem `bankBic`. */
    public function withBankBic(?string $bankBic): self
    {
        return clone($this, ['bankBic' => $bankBic]);
    }

    /** Kopie mit geändertem `bankSwift`. */
    public function withBankSwift(?string $bankSwift): self
    {
        return clone($this, ['bankSwift' => $bankSwift]);
    }

    /** Kopie mit geändertem `currencyCode`. */
    public function withCurrencyCode(?string $currencyCode): self
    {
        return clone($this, ['currencyCode' => $currencyCode]);
    }

    /** Kopie mit geändertem `creditorIdentifier`. */
    public function withCreditorIdentifier(?string $creditorIdentifier): self
    {
        return clone($this, ['creditorIdentifier' => $creditorIdentifier]);
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
            'client_number' => $this->clientNumber,
            'salutation' => $this->salutation,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'street' => $this->street,
            'zip' => $this->zip,
            'city' => $this->city,
            'state' => $this->state,
            'country_code' => $this->countryCode,
            'note' => $this->note,
            'email' => $this->email,
            'phone' => $this->phone,
            'fax' => $this->fax,
            'mobile' => $this->mobile,
            'www' => $this->www,
            'tax_number' => $this->taxNumber,
            'vat_number' => $this->vatNumber,
            'bank_account_number' => $this->bankAccountNumber,
            'bank_account_owner' => $this->bankAccountOwner,
            'bank_number' => $this->bankNumber,
            'bank_name' => $this->bankName,
            'bank_iban' => $this->bankIban,
            'bank_bic' => $this->bankBic,
            'bank_swift' => $this->bankSwift,
            'currency_code' => $this->currencyCode,
            'creditor_identifier' => $this->creditorIdentifier,
        ], static fn (mixed $v): bool => null !== $v);
    }
}
