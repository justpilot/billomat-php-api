<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

/**
 * Typisierter Payload für PUT /suppliers/{id} gemäß Billomat-API.
 *
 * Nur gesetzte (nicht-null) Felder werden serialisiert (Partial Update).
 *
 * Doku:
 * https://www.billomat.com/en/api/suppliers/
 *
 * Generiert aus tools/codegen/definitions/suppliers.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final readonly class SupplierUpdateOptions implements WriteOptions
{
    public function __construct(
        /**
         * Firmenname des Lieferanten.
         *
         * Billomat-Feld: name
         * Typ: ALNUM
         */
        public string|Clear|null $name = null,
        /**
         * Eigene Kundennummer beim Lieferanten.
         *
         * Billomat-Feld: client_number
         * Typ: ALNUM
         */
        public string|Clear|null $clientNumber = null,
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
         * Notiz zum Lieferanten.
         *
         * Billomat-Feld: note
         * Typ: ALNUM
         */
        public string|Clear|null $note = null,
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
         * Website (ohne Schema).
         *
         * Beispiel: "example.com"
         *
         * Billomat-Feld: www
         * Typ: URL (ohne http)
         */
        public string|Clear|null $www = null,
        /**
         * Steuernummer.
         *
         * Billomat-Feld: tax_number
         * Typ: ALNUM
         */
        public string|Clear|null $taxNumber = null,
        /**
         * Umsatzsteuer-ID (USt-IdNr.).
         *
         * Billomat-Feld: vat_number
         * Typ: gültige Ust-IdNr.
         */
        public string|Clear|null $vatNumber = null,
        /**
         * Kontonummer.
         *
         * Billomat-Feld: bank_account_number
         * Typ: ALNUM
         */
        public string|Clear|null $bankAccountNumber = null,
        /**
         * Kontoinhaber.
         *
         * Billomat-Feld: bank_account_owner
         * Typ: ALNUM
         */
        public string|Clear|null $bankAccountOwner = null,
        /**
         * Bankleitzahl.
         *
         * Billomat-Feld: bank_number
         * Typ: ALNUM
         */
        public string|Clear|null $bankNumber = null,
        /**
         * Bankname.
         *
         * Billomat-Feld: bank_name
         * Typ: ALNUM
         */
        public string|Clear|null $bankName = null,
        /**
         * IBAN.
         *
         * Billomat-Feld: bank_iban
         * Typ: gültige IBAN
         */
        public string|Clear|null $bankIban = null,
        /**
         * SWIFT / BIC.
         *
         * Billomat liefert den BIC als `bank_swift`; `bank_bic` dient nur als Fallback.
         *
         * Billomat-Feld: bank_bic
         */
        public string|Clear|null $bankBic = null,
        /**
         * SWIFT / BIC laut Billomat-Doku.
         *
         * Alias zu `bankBic` – beide werden serialisiert.
         *
         * Billomat-Feld: bank_swift
         * Typ: ALNUM
         */
        public string|Clear|null $bankSwift = null,
        /**
         * Währung des Lieferanten (ISO-Währungscode).
         *
         * Leer = Account-Währung.
         *
         * Billomat-Feld: currency_code
         * Typ: ISO-Währungscode
         */
        public string|Clear|null $currencyCode = null,
        /**
         * SEPA-Gläubiger-ID.
         *
         * Billomat-Feld: creditor_identifier
         * Typ: gültige Gläubiger-ID
         */
        public string|Clear|null $creditorIdentifier = null,
    ) {
    }

    /** Kopie mit geändertem `name`. */
    public function withName(string|Clear|null $name): self
    {
        return clone($this, ['name' => $name]);
    }

    /** Kopie mit geändertem `clientNumber`. */
    public function withClientNumber(string|Clear|null $clientNumber): self
    {
        return clone($this, ['clientNumber' => $clientNumber]);
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

    /** Kopie mit geändertem `note`. */
    public function withNote(string|Clear|null $note): self
    {
        return clone($this, ['note' => $note]);
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

    /** Kopie mit geändertem `www`. */
    public function withWww(string|Clear|null $www): self
    {
        return clone($this, ['www' => $www]);
    }

    /** Kopie mit geändertem `taxNumber`. */
    public function withTaxNumber(string|Clear|null $taxNumber): self
    {
        return clone($this, ['taxNumber' => $taxNumber]);
    }

    /** Kopie mit geändertem `vatNumber`. */
    public function withVatNumber(string|Clear|null $vatNumber): self
    {
        return clone($this, ['vatNumber' => $vatNumber]);
    }

    /** Kopie mit geändertem `bankAccountNumber`. */
    public function withBankAccountNumber(string|Clear|null $bankAccountNumber): self
    {
        return clone($this, ['bankAccountNumber' => $bankAccountNumber]);
    }

    /** Kopie mit geändertem `bankAccountOwner`. */
    public function withBankAccountOwner(string|Clear|null $bankAccountOwner): self
    {
        return clone($this, ['bankAccountOwner' => $bankAccountOwner]);
    }

    /** Kopie mit geändertem `bankNumber`. */
    public function withBankNumber(string|Clear|null $bankNumber): self
    {
        return clone($this, ['bankNumber' => $bankNumber]);
    }

    /** Kopie mit geändertem `bankName`. */
    public function withBankName(string|Clear|null $bankName): self
    {
        return clone($this, ['bankName' => $bankName]);
    }

    /** Kopie mit geändertem `bankIban`. */
    public function withBankIban(string|Clear|null $bankIban): self
    {
        return clone($this, ['bankIban' => $bankIban]);
    }

    /** Kopie mit geändertem `bankBic`. */
    public function withBankBic(string|Clear|null $bankBic): self
    {
        return clone($this, ['bankBic' => $bankBic]);
    }

    /** Kopie mit geändertem `bankSwift`. */
    public function withBankSwift(string|Clear|null $bankSwift): self
    {
        return clone($this, ['bankSwift' => $bankSwift]);
    }

    /** Kopie mit geändertem `currencyCode`. */
    public function withCurrencyCode(string|Clear|null $currencyCode): self
    {
        return clone($this, ['currencyCode' => $currencyCode]);
    }

    /** Kopie mit geändertem `creditorIdentifier`. */
    public function withCreditorIdentifier(string|Clear|null $creditorIdentifier): self
    {
        return clone($this, ['creditorIdentifier' => $creditorIdentifier]);
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
            ], static fn (mixed $v): bool => null !== $v),
        );
    }
}
