<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

/**
 * Repräsentiert einen Kunden (Client) aus der Billomat-API.
 *
 * Dieses Model bildet die Struktur von GET /clients bzw. GET /clients/{id} ab.
 * Es ist als Read-Model gedacht: Die Erstellung erfolgt über ClientCreateOptions.
 */
final readonly class Client
{
    /** Interne Billomat-ID des Kunden. */
    public ?int $id;

    /** Firmenname des Kunden. */
    public string $name;

    /** Kundennummer (frei wählbar). */
    public ?string $clientNumber;

    /** Straße. */
    public ?string $street;

    /** Postleitzahl. */
    public ?string $zip;

    /** Ort. */
    public ?string $city;

    /** Bundesland / Bezirk / Region. */
    public ?string $state;

    /**
     * Ländercode des Kunden (ISO 3166-1 Alpha-2).
     *
     * Beispiel: "DE", "AT", "CH"
     */
    public ?string $countryCode;

    /** Vorname. */
    public ?string $firstName;

    /** Nachname. */
    public ?string $lastName;

    /** Anrede. */
    public ?string $salutation;

    /** E-Mail-Adresse. */
    public ?string $email;

    /** Telefonnummer. */
    public ?string $phone;

    /** Faxnummer. */
    public ?string $fax;

    /** Mobilnummer. */
    public ?string $mobile;

    /** Website (ohne Schema). */
    public ?string $www;

    /** Notiz zum Kunden. */
    public ?string $note;

    /**
     * Gebietsschema des Kunden.
     *
     * Beispiel: "de_DE"
     */
    public ?string $locale;

    /** Steuernummer. */
    public ?string $taxNumber;

    /** Umsatzsteuer-ID (USt-IdNr.). */
    public ?string $vatNumber;

    /**
     * Steuerregel.
     *
     * Mögliche Werte: TAX, NO_TAX, COUNTRY
     */
    public ?string $taxRule;

    /**
     * Preisbasis.
     *
     * Mögliche Werte: NET, GROSS, SETTINGS
     */
    public ?string $netGross;

    /**
     * Währung des Kunden (ISO-Währungscode).
     *
     * Beispiel: "EUR"
     */
    public ?string $currencyCode;

    /**
     * Debitorennummer.
     *
     * Wird häufig als Referenz zur FIBU verwendet.
     */
    public ?int $debitorAccountNumber;

    /**
     * Preisgruppe.
     *
     * Artikel können mehrere Preise haben – die Preisgruppe legt fest,
     * welcher Preis für den Kunden verwendet wird.
     */
    public ?int $priceGroup;

    /**
     * Archivierungsstatus.
     *
     * 1 = archiviert, 0 = aktiv.
     */
    public ?bool $archived;

    /**
     * Automatischer Mahnlauf.
     *
     * true = Kunde wird in automatischem Mahnlauf berücksichtigt.
     */
    public ?bool $dunningRun;

    /**
     * Rabatt in Prozent.
     */
    public ?float $reduction;

    /** Skonto-Satz. */
    public ?float $discountRate;

    /** Skonto-Tage. */
    public ?float $discountDays;

    /** Fälligkeit in Tagen nach Rechnungsdatum. */
    public ?int $dueDays;

    /** Mahnfälligkeit in Tagen. */
    public ?int $reminderDueDays;

    /** Angebotsgültigkeit in Tagen. */
    public ?int $offerValidityDays;

    public function __construct(
        ?int    $id,
        string  $name,
        ?string $clientNumber = null,
        ?string $street = null,
        ?string $zip = null,
        ?string $city = null,
        ?string $state = null,
        ?string $countryCode = null,
        ?string $firstName = null,
        ?string $lastName = null,
        ?string $salutation = null,
        ?string $email = null,
        ?string $phone = null,
        ?string $fax = null,
        ?string $mobile = null,
        ?string $www = null,
        ?string $note = null,
        ?string $locale = null,
        ?string $taxNumber = null,
        ?string $vatNumber = null,
        ?string $taxRule = null,
        ?string $netGross = null,
        ?string $currencyCode = null,
        ?int    $debitorAccountNumber = null,
        ?int    $priceGroup = null,
        ?bool   $archived = null,
        ?bool   $dunningRun = null,
        ?float  $reduction = null,
        ?float  $discountRate = null,
        ?float  $discountDays = null,
        ?int    $dueDays = null,
        ?int    $reminderDueDays = null,
        ?int    $offerValidityDays = null,
    )
    {
        $this->id = $id;
        $this->name = $name;
        $this->clientNumber = $clientNumber;
        $this->street = $street;
        $this->zip = $zip;
        $this->city = $city;
        $this->state = $state;
        $this->countryCode = $countryCode;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->salutation = $salutation;
        $this->email = $email;
        $this->phone = $phone;
        $this->fax = $fax;
        $this->mobile = $mobile;
        $this->www = $www;
        $this->note = $note;
        $this->locale = $locale;
        $this->taxNumber = $taxNumber;
        $this->vatNumber = $vatNumber;
        $this->taxRule = $taxRule;
        $this->netGross = $netGross;
        $this->currencyCode = $currencyCode;
        $this->debitorAccountNumber = $debitorAccountNumber;
        $this->priceGroup = $priceGroup;
        $this->archived = $archived;
        $this->dunningRun = $dunningRun;
        $this->reduction = $reduction;
        $this->discountRate = $discountRate;
        $this->discountDays = $discountDays;
        $this->dueDays = $dueDays;
        $this->reminderDueDays = $reminderDueDays;
        $this->offerValidityDays = $offerValidityDays;
    }

    /**
     * Hydriert einen Client aus einem Billomat-Response-Array.
     *
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int)$data['id'] : null,
            name: (string)($data['name'] ?? ''),
            clientNumber: $data['client_number'] ?? null,
            street: $data['street'] ?? null,
            zip: $data['zip'] ?? null,
            city: $data['city'] ?? null,
            state: $data['state'] ?? null,
            countryCode: $data['country_code'] ?? null,
            firstName: $data['first_name'] ?? null,
            lastName: $data['last_name'] ?? null,
            salutation: $data['salutation'] ?? null,
            email: $data['email'] ?? null,
            phone: $data['phone'] ?? null,
            fax: $data['fax'] ?? null,
            mobile: $data['mobile'] ?? null,
            www: $data['www'] ?? null,
            note: $data['note'] ?? null,
            locale: $data['locale'] ?? null,
            taxNumber: $data['tax_number'] ?? null,
            vatNumber: $data['vat_number'] ?? null,
            taxRule: $data['tax_rule'] ?? null,
            netGross: $data['net_gross'] ?? null,
            currencyCode: $data['currency_code'] ?? null,
            debitorAccountNumber: isset($data['debitor_account_number']) ? (int)$data['debitor_account_number'] : null,
            priceGroup: isset($data['price_group']) ? (int)$data['price_group'] : null,
            archived: isset($data['archived']) ? (bool)$data['archived'] : null,
            dunningRun: isset($data['dunning_run']) ? (bool)$data['dunning_run'] : null,
            reduction: isset($data['reduction']) ? (float)$data['reduction'] : null,
            discountRate: isset($data['discount_rate']) ? (float)$data['discount_rate'] : null,
            discountDays: isset($data['discount_days']) ? (float)$data['discount_days'] : null,
            dueDays: isset($data['due_days']) ? (int)$data['due_days'] : null,
            reminderDueDays: isset($data['reminder_due_days']) ? (int)$data['reminder_due_days'] : null,
            offerValidityDays: isset($data['offer_validity_days']) ? (int)$data['offer_validity_days'] : null,
        );
    }

    /**
     * Exportiert den Client als Array mit Billomat-Feldnamen.
     *
     * Praktisch für Debugging oder Log-Ausgaben.
     *
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'client_number' => $this->clientNumber,
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
            'archived' => $this->archived,
            'dunning_run' => $this->dunningRun,
            'reduction' => $this->reduction,
            'discount_rate' => $this->discountRate,
            'discount_days' => $this->discountDays,
            'due_days' => $this->dueDays,
            'reminder_due_days' => $this->reminderDueDays,
            'offer_validity_days' => $this->offerValidityDays,
        ];
    }
}