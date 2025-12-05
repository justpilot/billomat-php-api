<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

/**
 * Typisierter Payload für POST /clients gemäß Billomat-API.
 *
 * Alle Felder basieren auf der offiziellen Dokumentation:
 * https://www.billomat.com/api/clients/
 *
 * Null-Werte bedeuten "nicht gesetzt" und werden beim Serialisieren ignoriert.
 */
final class ClientCreateOptions
{
    /**
     * Firmenname des Kunden.
     *
     * Billomat-Feld: name
     * Typ: ALNUM
     */
    public ?string $name = null;

    /**
     * Archivierungsstatus.
     *
     * 1 = archiviert, 0 = aktiv.
     *
     * Billomat-Feld: archived
     * Typ: BOOL
     * Default: 0
     */
    public ?bool $archived = null;

    /**
     * Präfix der Kundennummer.
     *
     * Billomat-Feld: number_pre
     * Typ: ALNUM
     * Default: Wert aus Einstellungen
     */
    public ?string $numberPre = null;

    /**
     * Laufende Kundennummer.
     *
     * Billomat-Feld: number
     * Typ: INT
     * Default: nächste freie Nummer
     */
    public ?int $number = null;

    /**
     * Mindestlänge der Kundennummer.
     *
     * Billomat-Feld: number_length
     * Typ: INT
     * Default: Wert aus Einstellungen
     */
    public ?int $numberLength = null;

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
     * Bundesland / Region / Bezirk.
     *
     * Billomat-Feld: state
     * Typ: ALNUM
     */
    public ?string $state = null;

    /**
     * Ländercode ISO 3166-1 Alpha-2.
     *
     * Billomat-Feld: country_code
     * Typ: ALNUM
     * Default: Land des eigenen Accounts
     *
     * Beispiel: "DE", "AT", "CH"
     */
    public ?string $countryCode = null;

    /**
     * Kundennummer (benutzerdefiniert).
     *
     * Billomat-Feld: client_number
     * Typ: ALNUM
     */
    public ?string $clientNumber = null;

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
     * Anrede.
     *
     * Billomat-Feld: salutation
     * Typ: ALNUM
     */
    public ?string $salutation = null;

    /**
     * E-Mail-Adresse.
     *
     * Billomat-Feld: email
     * Typ: gültige E-Mail
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
     * Website ohne http:// oder https://.
     *
     * Billomat-Feld: www
     * Typ: URL
     * Beispiel: "example.com"
     */
    public ?string $www = null;

    /**
     * Notiz zum Kunden.
     *
     * Billomat-Feld: note
     * Typ: ALNUM
     */
    public ?string $note = null;

    /**
     * Gebietsschema des Kunden.
     *
     * Billomat-Feld: locale
     * Typ: ALNUM
     * Beispiel: "de_DE"
     */
    public ?string $locale = null;

    // ---------------------------------------------------------------------
    // Steuerdaten
    // ---------------------------------------------------------------------

    /**
     * Steuernummer.
     *
     * Billomat-Feld: tax_number
     * Typ: ALNUM
     */
    public ?string $taxNumber = null;

    /**
     * Umsatzsteuer-ID.
     *
     * Billomat-Feld: vat_number
     * Typ: gültige USt-ID
     */
    public ?string $vatNumber = null;

    /**
     * Steuerregel.
     *
     * Billomat-Feld: tax_rule
     * Typ: TAX | NO_TAX | COUNTRY
     * Default: COUNTRY
     */
    public ?string $taxRule = null;

    /**
     * Preisbasis.
     *
     * Billomat-Feld: net_gross
     * Typ: NET | GROSS | SETTINGS
     * Default: SETTINGS
     */
    public ?string $netGross = null;

    /**
     * Währungscode.
     *
     * Billomat-Feld: currency_code
     * Typ: ISO-Währungscode
     * Beispiel: "EUR"
     */
    public ?string $currencyCode = null;

    // ---------------------------------------------------------------------
    // Debitor & Preisgruppe
    // ---------------------------------------------------------------------

    /**
     * Debitorennummer.
     *
     * Billomat-Feld: debitor_account_number
     * Typ: INT
     */
    public ?int $debitorAccountNumber = null;

    /**
     * Preisgruppe.
     *
     * Billomat-Feld: price_group
     * Typ: INT
     */
    public ?int $priceGroup = null;

    // ---------------------------------------------------------------------
    // Bankdaten
    // ---------------------------------------------------------------------

    /**
     * Kontonummer.
     *
     * Billomat-Feld: bank_account_number
     * Typ: ALNUM
     */
    public ?string $bankAccountNumber = null;

    /**
     * Kontoinhaber.
     *
     * Billomat-Feld: bank_account_owner
     * Typ: ALNUM
     */
    public ?string $bankAccountOwner = null;

    /**
     * Bankleitzahl.
     *
     * Billomat-Feld: bank_number
     * Typ: ALNUM
     */
    public ?string $bankNumber = null;

    /**
     * Bankname.
     *
     * Billomat-Feld: bank_name
     * Typ: ALNUM
     */
    public ?string $bankName = null;

    /**
     * SWIFT/BIC.
     *
     * Billomat-Feld: bank_swift
     * Typ: ALNUM
     */
    public ?string $bankSwift = null;

    /**
     * IBAN.
     *
     * Billomat-Feld: bank_iban
     * Typ: gültige IBAN
     */
    public ?string $bankIban = null;

    // ---------------------------------------------------------------------
    // SEPA
    // ---------------------------------------------------------------------

    /**
     * Mandatsreferenz für SEPA-Lastschrift.
     *
     * Billomat-Feld: sepa_mandate
     * Typ: ALNUM
     */
    public ?string $sepaMandate = null;

    /**
     * Ausstelldatum des SEPA-Mandats (YYYY-MM-DD).
     *
     * Billomat-Feld: sepa_mandate_date
     * Typ: DATE
     */
    public ?string $sepaMandateDate = null;

    // ---------------------------------------------------------------------
    // Zahlungsbedingungen
    // ---------------------------------------------------------------------

    /**
     * Standard-Zahlarten des Kunden (CSV).
     *
     * Billomat-Feld: default_payment_types
     * Typ: ALNUM (CSV)
     * Beispiel: "CASH,BANK_TRANSFER"
     */
    public ?string $defaultPaymentTypes = null;

    /**
     * Rabatt in Prozent.
     *
     * Billomat-Feld: reduction
     * Typ: FLOAT
     */
    public ?float $reduction = null;

    /**
     * Typ des Skonto-Satzes.
     *
     * Billomat-Feld: discount_rate_type
     * Typ: SETTINGS | ABSOLUTE | RELATIVE
     * Default: SETTINGS
     */
    public ?string $discountRateType = null;

    /**
     * Skonto-Satz.
     *
     * Billomat-Feld: discount_rate
     * Typ: FLOAT
     */
    public ?float $discountRate = null;

    /**
     * Typ des Skonto-Zeitraums.
     *
     * Billomat-Feld: discount_days_type
     * Typ: SETTINGS | ABSOLUTE | RELATIVE
     */
    public ?string $discountDaysType = null;

    /**
     * Skonto-Tage.
     *
     * Billomat-Feld: discount_days
     * Typ: FLOAT
     */
    public ?float $discountDays = null;

    /**
     * Typ der Fälligkeit.
     *
     * Billomat-Feld: due_days_type
     * Typ: SETTINGS | ABSOLUTE | RELATIVE
     */
    public ?string $dueDaysType = null;

    /**
     * Fälligkeit in Tagen nach Rechnungsdatum.
     *
     * Billomat-Feld: due_days
     * Typ: INT
     */
    public ?int $dueDays = null;

    /**
     * Typ der Mahnfälligkeit.
     *
     * Billomat-Feld: reminder_due_days_type
     * Typ: SETTINGS | ABSOLUTE | RELATIVE
     */
    public ?string $reminderDueDaysType = null;

    /**
     * Mahnfälligkeit in Tagen.
     *
     * Billomat-Feld: reminder_due_days
     * Typ: INT
     */
    public ?int $reminderDueDays = null;

    /**
     * Typ der Angebotsgültigkeit.
     *
     * Billomat-Feld: offer_validity_days_type
     * Typ: SETTINGS | ABSOLUTE | RELATIVE
     */
    public ?string $offerValidityDaysType = null;

    /**
     * Angebotsgültigkeit (Tage).
     *
     * Billomat-Feld: offer_validity_days
     * Typ: INT
     */
    public ?int $offerValidityDays = null;

    // ---------------------------------------------------------------------

    /**
     * Automatischer Mahnlauf.
     *
     * Billomat-Feld: dunning_run
     * Typ: BOOL
     */
    public bool $dunningRun = false;

    /**
     * Serialisiert die Options in ein Billomat-Payload-Array.
     *
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        $data = [
            'name' => $this->name,
            'archived' => $this->archived,
            'number_pre' => $this->numberPre,
            'number' => $this->number,
            'number_length' => $this->numberLength,
            'street' => $this->street,
            'zip' => $this->zip,
            'city' => $this->city,
            'state' => $this->state,
            'country_code' => $this->countryCode,
            'client_number' => $this->clientNumber,
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

            'bank_account_number' => $this->bankAccountNumber,
            'bank_account_owner' => $this->bankAccountOwner,
            'bank_number' => $this->bankNumber,
            'bank_name' => $this->bankName,
            'bank_swift' => $this->bankSwift,
            'bank_iban' => $this->bankIban,

            'sepa_mandate' => $this->sepaMandate,
            'sepa_mandate_date' => $this->sepaMandateDate,

            'default_payment_types' => $this->defaultPaymentTypes,
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

            'dunning_run' => $this->dunningRun,
        ];

        // Null-Felder entfernen
        return array_filter($data, static fn($v) => $v !== null);
    }
}