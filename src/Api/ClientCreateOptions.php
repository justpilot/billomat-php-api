<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\Enum\InvoicePaymentType;
use Justpilot\Billomat\Model\Enum\NetGross;
use Justpilot\Billomat\Model\Enum\TaxRule;
use Justpilot\Billomat\Model\Enum\ValueType;

/**
 * Typisierter Payload für POST /clients gemäß Billomat-API.
 *
 * Null-Werte bedeuten "nicht gesetzt" und werden beim Serialisieren ignoriert.
 *
 * Doku:
 * https://www.billomat.com/en/api/clients/
 *
 * Generiert aus tools/codegen/definitions/clients.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final readonly class ClientCreateOptions implements WriteOptions
{
    public function __construct(
        /**
         * Firmenname des Kunden.
         *
         * Billomat-Feld: name
         * Typ: ALNUM
         */
        public ?string $name = null,
        /**
         * Kundennummer (frei wählbar).
         *
         * Billomat-Feld: client_number
         */
        public ?string $clientNumber = null,
        /**
         * Laufende Nummer (ohne Präfix).
         *
         * Billomat-Feld: number
         * Typ: INT
         * Default: nächste freie Nummer
         */
        public ?int $number = null,
        /**
         * Präfix der Kundennummer.
         *
         * Billomat-Feld: number_pre
         * Typ: ALNUM
         * Default: Wert aus Einstellungen
         */
        public ?string $numberPre = null,
        /**
         * Mindestlänge der Kundennummer.
         *
         * Billomat-Feld: number_length
         * Typ: INT
         * Default: Wert aus den Einstellungen
         */
        public ?int $numberLength = null,
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
         * Ländercode des Kunden (ISO 3166-1 Alpha-2).
         *
         * Beispiel: "DE", "AT", "CH"
         *
         * Billomat-Feld: country_code
         * Typ: Ländercode nach ISO 3166 Alpha-2
         * Default: Wert aus eigener Firma
         */
        public ?string $countryCode = null,
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
         * Anrede.
         *
         * Billomat-Feld: salutation
         * Typ: ALNUM
         */
        public ?string $salutation = null,
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
         * Notiz zum Kunden.
         *
         * Billomat-Feld: note
         * Typ: ALNUM
         */
        public ?string $note = null,
        /**
         * Gebietsschema des Kunden.
         *
         * Beispiel: "de_DE"
         *
         * Billomat-Feld: locale
         * Typ: ALNUM
         */
        public ?string $locale = null,
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
         * Steuerregel.
         *
         * Billomat-Feld: tax_rule
         * Typ: TAX | NO_TAX | COUNTRY
         * Default: COUNTRY
         */
        public ?TaxRule $taxRule = null,
        /**
         * Preisbasis.
         *
         * Billomat-Feld: net_gross
         * Typ: NET | GROSS | SETTINGS
         * Default: SETTINGS
         */
        public ?NetGross $netGross = null,
        /**
         * Währung des Kunden (ISO-Währungscode).
         *
         * Beispiel: "EUR"
         *
         * Billomat-Feld: currency_code
         * Typ: ISO-Währungscode
         */
        public ?string $currencyCode = null,
        /**
         * Debitorennummer.
         *
         * Wird häufig als Referenz zur FIBU verwendet.
         *
         * Billomat-Feld: debitor_account_number
         * Typ: INT
         */
        public ?int $debitorAccountNumber = null,
        /**
         * Preisgruppe.
         *
         * Artikel können mehrere Preise haben – die Preisgruppe legt fest,
         * welcher Preis für den Kunden verwendet wird.
         *
         * Billomat-Feld: price_group
         * Typ: INT
         */
        public ?int $priceGroup = null,
        /**
         * Archivierungsstatus.
         *
         * true = archiviert, false = aktiv.
         *
         * Billomat-Feld: archived
         * Typ: BOOL
         * Default: 0
         */
        public ?bool $archived = null,
        /**
         * Automatischer Mahnlauf.
         *
         * true = Kunde wird in automatischem Mahnlauf berücksichtigt.
         *
         * Billomat-Feld: dunning_run
         * Typ: BOOL
         */
        public ?bool $dunningRun = null,
        /**
         * Rabatt in Prozent.
         *
         * Billomat-Feld: reduction
         * Typ: FLOAT
         */
        public ?float $reduction = null,
        /**
         * Typ des Skonto-Satzes.
         *
         * Billomat-Feld: discount_rate_type
         * Typ: SETTINGS | ABSOLUTE | RELATIVE
         * Default: SETTINGS
         */
        public ?ValueType $discountRateType = null,
        /**
         * Skonto-Satz.
         *
         * Billomat-Feld: discount_rate
         * Typ: FLOAT
         */
        public ?float $discountRate = null,
        /**
         * Typ des Skonto-Zeitraums.
         *
         * Billomat-Feld: discount_days_type
         * Typ: SETTINGS | ABSOLUTE | RELATIVE
         * Default: SETTINGS
         */
        public ?ValueType $discountDaysType = null,
        /**
         * Skonto-Tage.
         *
         * Billomat-Feld: discount_days
         * Typ: FLOAT
         */
        public ?float $discountDays = null,
        /**
         * Typ der Fälligkeit.
         *
         * Billomat-Feld: due_days_type
         * Typ: SETTINGS | ABSOLUTE | RELATIVE
         * Default: SETTINGS
         */
        public ?ValueType $dueDaysType = null,
        /**
         * Fälligkeit in Tagen nach Rechnungsdatum.
         *
         * Billomat-Feld: due_days
         * Typ: INT
         */
        public ?int $dueDays = null,
        /**
         * Typ der Mahnfälligkeit.
         *
         * Billomat-Feld: reminder_due_days_type
         * Typ: SETTINGS | ABSOLUTE | RELATIVE
         * Default: SETTINGS
         */
        public ?ValueType $reminderDueDaysType = null,
        /**
         * Mahnfälligkeit in Tagen.
         *
         * Billomat-Feld: reminder_due_days
         * Typ: INT
         */
        public ?int $reminderDueDays = null,
        /**
         * Typ der Angebotsgültigkeit.
         *
         * Billomat-Feld: offer_validity_days_type
         * Typ: SETTINGS | ABSOLUTE | RELATIVE
         * Default: SETTINGS
         */
        public ?ValueType $offerValidityDaysType = null,
        /**
         * Angebotsgültigkeit in Tagen.
         *
         * Billomat-Feld: offer_validity_days
         * Typ: INT
         */
        public ?int $offerValidityDays = null,
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
         * Kontonummer.
         *
         * Billomat-Feld: bank_account_number
         * Typ: ALNUM
         */
        public ?string $bankAccountNumber = null,
        /**
         * SWIFT / BIC.
         *
         * Billomat-Feld: bank_swift
         * Typ: ALNUM
         */
        public ?string $bankSwift = null,
        /**
         * IBAN.
         *
         * Billomat-Feld: bank_iban
         * Typ: gültige IBAN
         */
        public ?string $bankIban = null,
        /**
         * SEPA-Mandatsreferenz.
         *
         * Billomat-Feld: sepa_mandate
         * Typ: ALNUM
         */
        public ?string $sepaMandate = null,
        /**
         * Ausstelldatum des SEPA-Mandats.
         *
         * Format: YYYY-MM-DD
         *
         * Billomat-Feld: sepa_mandate_date
         * Typ: DATE
         */
        public ?string $sepaMandateDate = null,
        /**
         * Standard-Zahlarten.
         *
         * Billomat überträgt sie kommasepariert, z. B. "CASH,BANK_TRANSFER".
         *
         * Billomat-Feld: default_payment_types
         * Typ: ALNUM
         *
         * @var list<InvoicePaymentType>
         */
        public array $defaultPaymentTypes = [],
    ) {
    }

    /** Kopie mit geändertem `name`. */
    public function withName(?string $name): self
    {
        return clone($this, ['name' => $name]);
    }

    /** Kopie mit geändertem `clientNumber`. */
    public function withClientNumber(?string $clientNumber): self
    {
        return clone($this, ['clientNumber' => $clientNumber]);
    }

    /** Kopie mit geändertem `number`. */
    public function withNumber(?int $number): self
    {
        return clone($this, ['number' => $number]);
    }

    /** Kopie mit geändertem `numberPre`. */
    public function withNumberPre(?string $numberPre): self
    {
        return clone($this, ['numberPre' => $numberPre]);
    }

    /** Kopie mit geändertem `numberLength`. */
    public function withNumberLength(?int $numberLength): self
    {
        return clone($this, ['numberLength' => $numberLength]);
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

    /** Kopie mit geändertem `salutation`. */
    public function withSalutation(?string $salutation): self
    {
        return clone($this, ['salutation' => $salutation]);
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

    /** Kopie mit geändertem `note`. */
    public function withNote(?string $note): self
    {
        return clone($this, ['note' => $note]);
    }

    /** Kopie mit geändertem `locale`. */
    public function withLocale(?string $locale): self
    {
        return clone($this, ['locale' => $locale]);
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

    /** Kopie mit geändertem `taxRule`. */
    public function withTaxRule(?TaxRule $taxRule): self
    {
        return clone($this, ['taxRule' => $taxRule]);
    }

    /** Kopie mit geändertem `netGross`. */
    public function withNetGross(?NetGross $netGross): self
    {
        return clone($this, ['netGross' => $netGross]);
    }

    /** Kopie mit geändertem `currencyCode`. */
    public function withCurrencyCode(?string $currencyCode): self
    {
        return clone($this, ['currencyCode' => $currencyCode]);
    }

    /** Kopie mit geändertem `debitorAccountNumber`. */
    public function withDebitorAccountNumber(?int $debitorAccountNumber): self
    {
        return clone($this, ['debitorAccountNumber' => $debitorAccountNumber]);
    }

    /** Kopie mit geändertem `priceGroup`. */
    public function withPriceGroup(?int $priceGroup): self
    {
        return clone($this, ['priceGroup' => $priceGroup]);
    }

    /** Kopie mit geändertem `archived`. */
    public function withArchived(?bool $archived): self
    {
        return clone($this, ['archived' => $archived]);
    }

    /** Kopie mit geändertem `dunningRun`. */
    public function withDunningRun(?bool $dunningRun): self
    {
        return clone($this, ['dunningRun' => $dunningRun]);
    }

    /** Kopie mit geändertem `reduction`. */
    public function withReduction(?float $reduction): self
    {
        return clone($this, ['reduction' => $reduction]);
    }

    /** Kopie mit geändertem `discountRateType`. */
    public function withDiscountRateType(?ValueType $discountRateType): self
    {
        return clone($this, ['discountRateType' => $discountRateType]);
    }

    /** Kopie mit geändertem `discountRate`. */
    public function withDiscountRate(?float $discountRate): self
    {
        return clone($this, ['discountRate' => $discountRate]);
    }

    /** Kopie mit geändertem `discountDaysType`. */
    public function withDiscountDaysType(?ValueType $discountDaysType): self
    {
        return clone($this, ['discountDaysType' => $discountDaysType]);
    }

    /** Kopie mit geändertem `discountDays`. */
    public function withDiscountDays(?float $discountDays): self
    {
        return clone($this, ['discountDays' => $discountDays]);
    }

    /** Kopie mit geändertem `dueDaysType`. */
    public function withDueDaysType(?ValueType $dueDaysType): self
    {
        return clone($this, ['dueDaysType' => $dueDaysType]);
    }

    /** Kopie mit geändertem `dueDays`. */
    public function withDueDays(?int $dueDays): self
    {
        return clone($this, ['dueDays' => $dueDays]);
    }

    /** Kopie mit geändertem `reminderDueDaysType`. */
    public function withReminderDueDaysType(?ValueType $reminderDueDaysType): self
    {
        return clone($this, ['reminderDueDaysType' => $reminderDueDaysType]);
    }

    /** Kopie mit geändertem `reminderDueDays`. */
    public function withReminderDueDays(?int $reminderDueDays): self
    {
        return clone($this, ['reminderDueDays' => $reminderDueDays]);
    }

    /** Kopie mit geändertem `offerValidityDaysType`. */
    public function withOfferValidityDaysType(?ValueType $offerValidityDaysType): self
    {
        return clone($this, ['offerValidityDaysType' => $offerValidityDaysType]);
    }

    /** Kopie mit geändertem `offerValidityDays`. */
    public function withOfferValidityDays(?int $offerValidityDays): self
    {
        return clone($this, ['offerValidityDays' => $offerValidityDays]);
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

    /** Kopie mit geändertem `bankAccountNumber`. */
    public function withBankAccountNumber(?string $bankAccountNumber): self
    {
        return clone($this, ['bankAccountNumber' => $bankAccountNumber]);
    }

    /** Kopie mit geändertem `bankSwift`. */
    public function withBankSwift(?string $bankSwift): self
    {
        return clone($this, ['bankSwift' => $bankSwift]);
    }

    /** Kopie mit geändertem `bankIban`. */
    public function withBankIban(?string $bankIban): self
    {
        return clone($this, ['bankIban' => $bankIban]);
    }

    /** Kopie mit geändertem `sepaMandate`. */
    public function withSepaMandate(?string $sepaMandate): self
    {
        return clone($this, ['sepaMandate' => $sepaMandate]);
    }

    /** Kopie mit geändertem `sepaMandateDate`. */
    public function withSepaMandateDate(?string $sepaMandateDate): self
    {
        return clone($this, ['sepaMandateDate' => $sepaMandateDate]);
    }

    /**
     * Kopie mit geändertem `defaultPaymentTypes`.
     *
     * @param list<InvoicePaymentType> $defaultPaymentTypes
     */
    public function withDefaultPaymentTypes(array $defaultPaymentTypes): self
    {
        return clone($this, ['defaultPaymentTypes' => $defaultPaymentTypes]);
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
            'tax_rule' => $this->taxRule?->value,
            'net_gross' => $this->netGross?->value,
            'currency_code' => $this->currencyCode,
            'debitor_account_number' => $this->debitorAccountNumber,
            'price_group' => $this->priceGroup,
            'archived' => $this->archived,
            'dunning_run' => $this->dunningRun,
            'reduction' => $this->reduction,
            'discount_rate_type' => $this->discountRateType?->value,
            'discount_rate' => $this->discountRate,
            'discount_days_type' => $this->discountDaysType?->value,
            'discount_days' => $this->discountDays,
            'due_days_type' => $this->dueDaysType?->value,
            'due_days' => $this->dueDays,
            'reminder_due_days_type' => $this->reminderDueDaysType?->value,
            'reminder_due_days' => $this->reminderDueDays,
            'offer_validity_days_type' => $this->offerValidityDaysType?->value,
            'offer_validity_days' => $this->offerValidityDays,
            'bank_account_owner' => $this->bankAccountOwner,
            'bank_number' => $this->bankNumber,
            'bank_name' => $this->bankName,
            'bank_account_number' => $this->bankAccountNumber,
            'bank_swift' => $this->bankSwift,
            'bank_iban' => $this->bankIban,
            'sepa_mandate' => $this->sepaMandate,
            'sepa_mandate_date' => $this->sepaMandateDate,
            'default_payment_types' => [] === $this->defaultPaymentTypes ? null : implode(',', array_map(static fn (InvoicePaymentType $v): string => $v->value, $this->defaultPaymentTypes)),
        ], static fn (mixed $v): bool => null !== $v);
    }
}
