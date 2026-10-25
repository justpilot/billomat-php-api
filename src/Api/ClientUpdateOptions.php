<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

/**
 * Typisierter Payload für PUT /clients/{id} gemäß Billomat-API.
 *
 * Nur gesetzte (nicht-null) Felder werden serialisiert (Partial Update).
 *
 * Hinweis: Ein Kunde kann laut Billomat nur bearbeitet werden, wenn er nicht
 * archiviert ist. Über das Feld `archived` selbst lässt sich der Archivstatus
 * aber umschalten.
 *
 * Doku:
 * https://www.billomat.com/en/api/clients/
 *
 * Generiert aus tools/codegen/definitions/clients.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final readonly class ClientUpdateOptions implements WriteOptions
{
    public function __construct(
        /**
         * Firmenname des Kunden.
         *
         * Billomat-Feld: name
         * Typ: ALNUM
         */
        public string|Clear|null $name = null,
        /**
         * Kundennummer (frei wählbar).
         *
         * Billomat-Feld: client_number
         */
        public string|Clear|null $clientNumber = null,
        /**
         * Laufende Nummer (ohne Präfix).
         *
         * Billomat-Feld: number
         * Typ: INT
         */
        public int|Clear|null $number = null,
        /**
         * Präfix der Kundennummer.
         *
         * Billomat-Feld: number_pre
         * Typ: ALNUM
         */
        public string|Clear|null $numberPre = null,
        /**
         * Mindestlänge der Kundennummer.
         *
         * Billomat-Feld: number_length
         * Typ: INT
         */
        public int|Clear|null $numberLength = null,
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
         * Ländercode des Kunden (ISO 3166-1 Alpha-2).
         *
         * Beispiel: "DE", "AT", "CH"
         *
         * Billomat-Feld: country_code
         * Typ: Ländercode nach ISO 3166 Alpha-2
         */
        public string|Clear|null $countryCode = null,
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
         * Anrede.
         *
         * Billomat-Feld: salutation
         * Typ: ALNUM
         */
        public string|Clear|null $salutation = null,
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
         * Notiz zum Kunden.
         *
         * Billomat-Feld: note
         * Typ: ALNUM
         */
        public string|Clear|null $note = null,
        /**
         * Gebietsschema des Kunden.
         *
         * Beispiel: "de_DE"
         *
         * Billomat-Feld: locale
         * Typ: ALNUM
         */
        public string|Clear|null $locale = null,
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
         * Steuerregel.
         *
         * Billomat-Feld: tax_rule
         * Typ: TAX | NO_TAX | COUNTRY
         */
        public string|Clear|null $taxRule = null,
        /**
         * Preisbasis.
         *
         * Billomat-Feld: net_gross
         * Typ: NET | GROSS | SETTINGS
         */
        public string|Clear|null $netGross = null,
        /**
         * Währung des Kunden (ISO-Währungscode).
         *
         * Beispiel: "EUR"
         *
         * Billomat-Feld: currency_code
         * Typ: ISO-Währungscode
         */
        public string|Clear|null $currencyCode = null,
        /**
         * Debitorennummer.
         *
         * Wird häufig als Referenz zur FIBU verwendet.
         *
         * Billomat-Feld: debitor_account_number
         * Typ: INT
         */
        public int|Clear|null $debitorAccountNumber = null,
        /**
         * Preisgruppe.
         *
         * Artikel können mehrere Preise haben – die Preisgruppe legt fest,
         * welcher Preis für den Kunden verwendet wird.
         *
         * Billomat-Feld: price_group
         * Typ: INT
         */
        public int|Clear|null $priceGroup = null,
        /**
         * Archivierungsstatus.
         *
         * true = archiviert, false = aktiv.
         *
         * Billomat-Feld: archived
         * Typ: BOOL
         */
        public bool|Clear|null $archived = null,
        /**
         * Automatischer Mahnlauf.
         *
         * true = Kunde wird in automatischem Mahnlauf berücksichtigt.
         *
         * Billomat-Feld: dunning_run
         * Typ: BOOL
         */
        public bool|Clear|null $dunningRun = null,
        /**
         * Rabatt in Prozent.
         *
         * Billomat-Feld: reduction
         * Typ: FLOAT
         */
        public float|Clear|null $reduction = null,
        /**
         * Typ des Skonto-Satzes.
         *
         * Billomat-Feld: discount_rate_type
         * Typ: SETTINGS | ABSOLUTE | RELATIVE
         */
        public string|Clear|null $discountRateType = null,
        /**
         * Skonto-Satz.
         *
         * Billomat-Feld: discount_rate
         * Typ: FLOAT
         */
        public float|Clear|null $discountRate = null,
        /**
         * Typ des Skonto-Zeitraums.
         *
         * Billomat-Feld: discount_days_type
         * Typ: SETTINGS | ABSOLUTE | RELATIVE
         */
        public string|Clear|null $discountDaysType = null,
        /**
         * Skonto-Tage.
         *
         * Billomat-Feld: discount_days
         * Typ: FLOAT
         */
        public float|Clear|null $discountDays = null,
        /**
         * Typ der Fälligkeit.
         *
         * Billomat-Feld: due_days_type
         * Typ: SETTINGS | ABSOLUTE | RELATIVE
         */
        public string|Clear|null $dueDaysType = null,
        /**
         * Fälligkeit in Tagen nach Rechnungsdatum.
         *
         * Billomat-Feld: due_days
         * Typ: INT
         */
        public int|Clear|null $dueDays = null,
        /**
         * Typ der Mahnfälligkeit.
         *
         * Billomat-Feld: reminder_due_days_type
         * Typ: SETTINGS | ABSOLUTE | RELATIVE
         */
        public string|Clear|null $reminderDueDaysType = null,
        /**
         * Mahnfälligkeit in Tagen.
         *
         * Billomat-Feld: reminder_due_days
         * Typ: INT
         */
        public int|Clear|null $reminderDueDays = null,
        /**
         * Typ der Angebotsgültigkeit.
         *
         * Billomat-Feld: offer_validity_days_type
         * Typ: SETTINGS | ABSOLUTE | RELATIVE
         */
        public string|Clear|null $offerValidityDaysType = null,
        /**
         * Angebotsgültigkeit in Tagen.
         *
         * Billomat-Feld: offer_validity_days
         * Typ: INT
         */
        public int|Clear|null $offerValidityDays = null,
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
         * Kontonummer.
         *
         * Billomat-Feld: bank_account_number
         * Typ: ALNUM
         */
        public string|Clear|null $bankAccountNumber = null,
        /**
         * SWIFT / BIC.
         *
         * Billomat-Feld: bank_swift
         * Typ: ALNUM
         */
        public string|Clear|null $bankSwift = null,
        /**
         * IBAN.
         *
         * Billomat-Feld: bank_iban
         * Typ: gültige IBAN
         */
        public string|Clear|null $bankIban = null,
        /**
         * SEPA-Mandatsreferenz.
         *
         * Billomat-Feld: sepa_mandate
         * Typ: ALNUM
         */
        public string|Clear|null $sepaMandate = null,
        /**
         * Ausstelldatum des SEPA-Mandats.
         *
         * Format: YYYY-MM-DD
         *
         * Billomat-Feld: sepa_mandate_date
         * Typ: DATE
         */
        public string|Clear|null $sepaMandateDate = null,
        /**
         * Standard-Zahlarten (CSV).
         *
         * Beispiel: "CASH,BANK_TRANSFER"
         *
         * Billomat-Feld: default_payment_types
         * Typ: ALNUM
         */
        public string|Clear|null $defaultPaymentTypes = null,
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

    /** Kopie mit geändertem `number`. */
    public function withNumber(int|Clear|null $number): self
    {
        return clone($this, ['number' => $number]);
    }

    /** Kopie mit geändertem `numberPre`. */
    public function withNumberPre(string|Clear|null $numberPre): self
    {
        return clone($this, ['numberPre' => $numberPre]);
    }

    /** Kopie mit geändertem `numberLength`. */
    public function withNumberLength(int|Clear|null $numberLength): self
    {
        return clone($this, ['numberLength' => $numberLength]);
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

    /** Kopie mit geändertem `salutation`. */
    public function withSalutation(string|Clear|null $salutation): self
    {
        return clone($this, ['salutation' => $salutation]);
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

    /** Kopie mit geändertem `note`. */
    public function withNote(string|Clear|null $note): self
    {
        return clone($this, ['note' => $note]);
    }

    /** Kopie mit geändertem `locale`. */
    public function withLocale(string|Clear|null $locale): self
    {
        return clone($this, ['locale' => $locale]);
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

    /** Kopie mit geändertem `taxRule`. */
    public function withTaxRule(string|Clear|null $taxRule): self
    {
        return clone($this, ['taxRule' => $taxRule]);
    }

    /** Kopie mit geändertem `netGross`. */
    public function withNetGross(string|Clear|null $netGross): self
    {
        return clone($this, ['netGross' => $netGross]);
    }

    /** Kopie mit geändertem `currencyCode`. */
    public function withCurrencyCode(string|Clear|null $currencyCode): self
    {
        return clone($this, ['currencyCode' => $currencyCode]);
    }

    /** Kopie mit geändertem `debitorAccountNumber`. */
    public function withDebitorAccountNumber(int|Clear|null $debitorAccountNumber): self
    {
        return clone($this, ['debitorAccountNumber' => $debitorAccountNumber]);
    }

    /** Kopie mit geändertem `priceGroup`. */
    public function withPriceGroup(int|Clear|null $priceGroup): self
    {
        return clone($this, ['priceGroup' => $priceGroup]);
    }

    /** Kopie mit geändertem `archived`. */
    public function withArchived(bool|Clear|null $archived): self
    {
        return clone($this, ['archived' => $archived]);
    }

    /** Kopie mit geändertem `dunningRun`. */
    public function withDunningRun(bool|Clear|null $dunningRun): self
    {
        return clone($this, ['dunningRun' => $dunningRun]);
    }

    /** Kopie mit geändertem `reduction`. */
    public function withReduction(float|Clear|null $reduction): self
    {
        return clone($this, ['reduction' => $reduction]);
    }

    /** Kopie mit geändertem `discountRateType`. */
    public function withDiscountRateType(string|Clear|null $discountRateType): self
    {
        return clone($this, ['discountRateType' => $discountRateType]);
    }

    /** Kopie mit geändertem `discountRate`. */
    public function withDiscountRate(float|Clear|null $discountRate): self
    {
        return clone($this, ['discountRate' => $discountRate]);
    }

    /** Kopie mit geändertem `discountDaysType`. */
    public function withDiscountDaysType(string|Clear|null $discountDaysType): self
    {
        return clone($this, ['discountDaysType' => $discountDaysType]);
    }

    /** Kopie mit geändertem `discountDays`. */
    public function withDiscountDays(float|Clear|null $discountDays): self
    {
        return clone($this, ['discountDays' => $discountDays]);
    }

    /** Kopie mit geändertem `dueDaysType`. */
    public function withDueDaysType(string|Clear|null $dueDaysType): self
    {
        return clone($this, ['dueDaysType' => $dueDaysType]);
    }

    /** Kopie mit geändertem `dueDays`. */
    public function withDueDays(int|Clear|null $dueDays): self
    {
        return clone($this, ['dueDays' => $dueDays]);
    }

    /** Kopie mit geändertem `reminderDueDaysType`. */
    public function withReminderDueDaysType(string|Clear|null $reminderDueDaysType): self
    {
        return clone($this, ['reminderDueDaysType' => $reminderDueDaysType]);
    }

    /** Kopie mit geändertem `reminderDueDays`. */
    public function withReminderDueDays(int|Clear|null $reminderDueDays): self
    {
        return clone($this, ['reminderDueDays' => $reminderDueDays]);
    }

    /** Kopie mit geändertem `offerValidityDaysType`. */
    public function withOfferValidityDaysType(string|Clear|null $offerValidityDaysType): self
    {
        return clone($this, ['offerValidityDaysType' => $offerValidityDaysType]);
    }

    /** Kopie mit geändertem `offerValidityDays`. */
    public function withOfferValidityDays(int|Clear|null $offerValidityDays): self
    {
        return clone($this, ['offerValidityDays' => $offerValidityDays]);
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

    /** Kopie mit geändertem `bankAccountNumber`. */
    public function withBankAccountNumber(string|Clear|null $bankAccountNumber): self
    {
        return clone($this, ['bankAccountNumber' => $bankAccountNumber]);
    }

    /** Kopie mit geändertem `bankSwift`. */
    public function withBankSwift(string|Clear|null $bankSwift): self
    {
        return clone($this, ['bankSwift' => $bankSwift]);
    }

    /** Kopie mit geändertem `bankIban`. */
    public function withBankIban(string|Clear|null $bankIban): self
    {
        return clone($this, ['bankIban' => $bankIban]);
    }

    /** Kopie mit geändertem `sepaMandate`. */
    public function withSepaMandate(string|Clear|null $sepaMandate): self
    {
        return clone($this, ['sepaMandate' => $sepaMandate]);
    }

    /** Kopie mit geändertem `sepaMandateDate`. */
    public function withSepaMandateDate(string|Clear|null $sepaMandateDate): self
    {
        return clone($this, ['sepaMandateDate' => $sepaMandateDate]);
    }

    /** Kopie mit geändertem `defaultPaymentTypes`. */
    public function withDefaultPaymentTypes(string|Clear|null $defaultPaymentTypes): self
    {
        return clone($this, ['defaultPaymentTypes' => $defaultPaymentTypes]);
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
                'number' => $this->number,
                'number_pre' => $this->numberPre,
                'number_length' => $this->numberLength,
                'street' => $this->street,
                'zip' => $this->zip,
                'city' => $this->city,
                'state' => $this->state,
                'country_code' => $this->countryCode,
                'first_name' => $this->firstName,
                'last_name' => $this->lastName,
                'salutation' => $this->salutation,
                'email' => $this->email,
                'phone' => $this->phone,
                'fax' => $this->fax,
                'mobile' => $this->mobile,
                'www' => $this->www,
                'note' => $this->note,
                'locale' => $this->locale,
                'tax_number' => $this->taxNumber,
                'vat_number' => $this->vatNumber,
                'tax_rule' => $this->taxRule,
                'net_gross' => $this->netGross,
                'currency_code' => $this->currencyCode,
                'debitor_account_number' => $this->debitorAccountNumber,
                'price_group' => $this->priceGroup,
                'archived' => \is_bool($this->archived) ? ($this->archived ? 1 : 0) : $this->archived,
                'dunning_run' => \is_bool($this->dunningRun) ? ($this->dunningRun ? 1 : 0) : $this->dunningRun,
                'reduction' => $this->reduction,
                'discount_rate_type' => $this->discountRateType,
                'discount_rate' => $this->discountRate,
                'discount_days_type' => $this->discountDaysType,
                'discount_days' => $this->discountDays,
                'due_days_type' => $this->dueDaysType,
                'due_days' => $this->dueDays,
                'reminder_due_days_type' => $this->reminderDueDaysType,
                'reminder_due_days' => $this->reminderDueDays,
                'offer_validity_days_type' => $this->offerValidityDaysType,
                'offer_validity_days' => $this->offerValidityDays,
                'bank_account_owner' => $this->bankAccountOwner,
                'bank_number' => $this->bankNumber,
                'bank_name' => $this->bankName,
                'bank_account_number' => $this->bankAccountNumber,
                'bank_swift' => $this->bankSwift,
                'bank_iban' => $this->bankIban,
                'sepa_mandate' => $this->sepaMandate,
                'sepa_mandate_date' => $this->sepaMandateDate,
                'default_payment_types' => $this->defaultPaymentTypes,
            ], static fn (mixed $v): bool => null !== $v),
        );
    }
}
