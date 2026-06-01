<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

/**
 * Typisierter Payload für PUT /clients/{id} gemäß Billomat-API.
 *
 * Nur gesetzte (nicht-null) Felder werden serialisiert (Partial Update).
 *
 * Hinweise:
 * - Ein Kunde kann laut Billomat nur bearbeitet werden, wenn er nicht archiviert ist.
 *   Über das Feld `archived` selbst lässt sich allerdings der Archivstatus toggeln.
 * - Felder, die hier fehlen, lassen sich beim Anlegen über {@see ClientCreateOptions}
 *   setzen — die API erlaubt sie aber auch im Update.
 *
 * Doku: https://www.billomat.com/en/api/clients/
 */
final class ClientUpdateOptions
{
    /** Archivierungsstatus (true=archiviert, false=nicht archiviert). */
    public ?bool $archived = null;

    // ---------------------------------------------------------------------
    // Stammdaten
    // ---------------------------------------------------------------------

    public ?string $name = null;
    public ?string $street = null;
    public ?string $zip = null;
    public ?string $city = null;
    public ?string $state = null;
    public ?string $countryCode = null;
    public ?string $firstName = null;
    public ?string $lastName = null;
    public ?string $salutation = null;
    public ?string $phone = null;
    public ?string $fax = null;
    public ?string $mobile = null;
    public ?string $email = null;

    /** Website ohne http:// / https://. */
    public ?string $www = null;

    public ?string $note = null;

    /** Gebietsschema, z. B. "de_DE". */
    public ?string $locale = null;

    // ---------------------------------------------------------------------
    // Kundennummer (Doku: changeable im Update)
    // ---------------------------------------------------------------------

    public ?string $clientNumber = null;
    public ?string $numberPre = null;
    public ?int $number = null;
    public ?int $numberLength = null;

    // ---------------------------------------------------------------------
    // Steuerdaten
    // ---------------------------------------------------------------------

    public ?string $taxNumber = null;
    public ?string $vatNumber = null;

    /** Mögliche Werte: TAX, NO_TAX, COUNTRY. */
    public ?string $taxRule = null;

    /** Preisbasis: NET, GROSS, SETTINGS. */
    public ?string $netGross = null;

    /** Währungscode (ISO), z. B. "EUR". */
    public ?string $currencyCode = null;

    // ---------------------------------------------------------------------
    // Debitor & Preisgruppe
    // ---------------------------------------------------------------------

    public ?int $debitorAccountNumber = null;
    public ?int $priceGroup = null;

    /** Automatischer Mahnlauf. */
    public ?bool $dunningRun = null;

    // ---------------------------------------------------------------------
    // Bankdaten
    // ---------------------------------------------------------------------

    public ?string $bankAccountNumber = null;
    public ?string $bankAccountOwner = null;
    public ?string $bankNumber = null;
    public ?string $bankName = null;
    public ?string $bankSwift = null;
    public ?string $bankIban = null;

    // ---------------------------------------------------------------------
    // SEPA
    // ---------------------------------------------------------------------

    public ?string $sepaMandate = null;

    /** Ausstelldatum des SEPA-Mandats (YYYY-MM-DD). */
    public ?string $sepaMandateDate = null;

    // ---------------------------------------------------------------------
    // Zahlungsbedingungen
    // ---------------------------------------------------------------------

    /** Standard-Zahlarten (CSV), z. B. "CASH,BANK_TRANSFER". */
    public ?string $defaultPaymentTypes = null;

    /** Rabatt in Prozent. */
    public ?float $reduction = null;

    /** Typ des Skonto-Satzes: SETTINGS|ABSOLUTE|RELATIVE. */
    public ?string $discountRateType = null;
    public ?float $discountRate = null;

    /** Typ des Skonto-Zeitraums: SETTINGS|ABSOLUTE|RELATIVE. */
    public ?string $discountDaysType = null;
    public ?float $discountDays = null;

    /** Typ der Fälligkeit: SETTINGS|ABSOLUTE|RELATIVE. */
    public ?string $dueDaysType = null;
    public ?int $dueDays = null;

    /** Typ der Mahnfälligkeit: SETTINGS|ABSOLUTE|RELATIVE. */
    public ?string $reminderDueDaysType = null;
    public ?int $reminderDueDays = null;

    /** Typ der Angebotsgültigkeit: SETTINGS|ABSOLUTE|RELATIVE. */
    public ?string $offerValidityDaysType = null;
    public ?int $offerValidityDays = null;

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        $data = [
            'archived' => null === $this->archived ? null : ($this->archived ? 1 : 0),
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
            'note' => $this->note,
            'locale' => $this->locale,

            'client_number' => $this->clientNumber,
            'number_pre' => $this->numberPre,
            'number' => $this->number,
            'number_length' => $this->numberLength,

            'tax_number' => $this->taxNumber,
            'vat_number' => $this->vatNumber,
            'tax_rule' => $this->taxRule,
            'net_gross' => $this->netGross,
            'currency_code' => $this->currencyCode,

            'debitor_account_number' => $this->debitorAccountNumber,
            'price_group' => $this->priceGroup,
            'dunning_run' => null === $this->dunningRun ? null : ($this->dunningRun ? 1 : 0),

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
        ];

        return array_filter($data, static fn (string|int|float|null $v): bool => null !== $v);
    }
}
