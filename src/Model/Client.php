<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use DateTimeImmutable;
use Justpilot\Billomat\Internal\ListCaster;
use Justpilot\Billomat\Internal\ScalarCaster;
use Justpilot\Billomat\Model\Enum\NetGross;
use Justpilot\Billomat\Model\Enum\PaymentType;
use Justpilot\Billomat\Model\Enum\TaxRule;
use Justpilot\Billomat\Model\Enum\ValueType;

use const DATE_ATOM;

/**
 * Repräsentiert einen Kunden (Client) aus der Billomat-API.
 *
 * Dieses Model bildet die Struktur von GET /clients bzw. GET /clients/{id} ab.
 * Es ist als Read-Model gedacht: Die Erstellung erfolgt über ClientCreateOptions.
 *
 * Dokumentation:
 * https://www.billomat.com/en/api/clients/
 *
 * Generiert aus tools/codegen/definitions/clients.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final readonly class Client
{
    /** Billomat-Felder, die das Model abbildet. */
    private const array FIELDS = [
        'id' => true,
        'name' => true,
        'created' => true,
        'client_number' => true,
        'number' => true,
        'number_pre' => true,
        'number_length' => true,
        'street' => true,
        'zip' => true,
        'city' => true,
        'state' => true,
        'country_code' => true,
        'address' => true,
        'first_name' => true,
        'last_name' => true,
        'salutation' => true,
        'email' => true,
        'phone' => true,
        'fax' => true,
        'mobile' => true,
        'www' => true,
        'note' => true,
        'locale' => true,
        'tax_number' => true,
        'vat_number' => true,
        'tax_rule' => true,
        'net_gross' => true,
        'currency_code' => true,
        'debitor_account_number' => true,
        'price_group' => true,
        'archived' => true,
        'dunning_run' => true,
        'reduction' => true,
        'discount_rate_type' => true,
        'discount_rate' => true,
        'discount_days_type' => true,
        'discount_days' => true,
        'due_days_type' => true,
        'due_days' => true,
        'reminder_due_days_type' => true,
        'reminder_due_days' => true,
        'offer_validity_days_type' => true,
        'offer_validity_days' => true,
        'bank_account_owner' => true,
        'bank_number' => true,
        'bank_name' => true,
        'bank_account_number' => true,
        'bank_swift' => true,
        'bank_iban' => true,
        'sepa_mandate' => true,
        'sepa_mandate_date' => true,
        'default_payment_types' => true,
        'enable_customerportal' => true,
        'revenue_gross' => true,
        'revenue_net' => true,
        'updated' => true,
        'customfield' => true,
        'facturx_identifier' => true,
        'seller_identifier' => true,
        'dig_exclude' => true,
    ];

    /**
     * @param list<PaymentType>    $defaultPaymentTypes
     * @param array<string, mixed> $extra
     */
    public function __construct(
        /** Interne Billomat-ID des Kunden. */
        public ?int $id,
        /** Firmenname des Kunden. */
        public string $name,
        /** Erstellungszeitpunkt des Kunden. */
        public ?DateTimeImmutable $created = null,
        /** Kundennummer (frei wählbar). */
        public ?string $clientNumber = null,
        /** Laufende Nummer (ohne Präfix). */
        public ?int $number = null,
        /** Präfix der Kundennummer. */
        public ?string $numberPre = null,
        /** Mindestlänge der Kundennummer. */
        public ?int $numberLength = null,
        /** Straße. */
        public ?string $street = null,
        /** Postleitzahl. */
        public ?string $zip = null,
        /** Ort. */
        public ?string $city = null,
        /** Bundesland / Bezirk / Region. */
        public ?string $state = null,
        /**
         * Ländercode des Kunden (ISO 3166-1 Alpha-2).
         *
         * Beispiel: "DE", "AT", "CH"
         */
        public ?string $countryCode = null,
        /** Von Billomat zusammengesetzte Adresse (mehrzeilig). */
        public ?string $address = null,
        /** Vorname. */
        public ?string $firstName = null,
        /** Nachname. */
        public ?string $lastName = null,
        /** Anrede. */
        public ?string $salutation = null,
        /** E-Mail-Adresse. */
        public ?string $email = null,
        /** Telefonnummer. */
        public ?string $phone = null,
        /** Faxnummer. */
        public ?string $fax = null,
        /** Mobilnummer. */
        public ?string $mobile = null,
        /**
         * Website (ohne Schema).
         *
         * Beispiel: "example.com"
         */
        public ?string $www = null,
        /** Notiz zum Kunden. */
        public ?string $note = null,
        /**
         * Gebietsschema des Kunden.
         *
         * Beispiel: "de_DE"
         */
        public ?string $locale = null,
        /** Steuernummer. */
        public ?string $taxNumber = null,
        /** Umsatzsteuer-ID (USt-IdNr.). */
        public ?string $vatNumber = null,
        /**
         * Steuerregel.
         *
         * Mögliche Werte: TAX, NO_TAX, COUNTRY
         */
        public ?TaxRule $taxRule = null,
        /**
         * Preisbasis.
         *
         * Mögliche Werte: NET, GROSS, SETTINGS
         */
        public ?NetGross $netGross = null,
        /**
         * Währung des Kunden (ISO-Währungscode).
         *
         * Beispiel: "EUR"
         */
        public ?string $currencyCode = null,
        /**
         * Debitorennummer.
         *
         * Wird häufig als Referenz zur FIBU verwendet.
         */
        public ?int $debitorAccountNumber = null,
        /**
         * Preisgruppe.
         *
         * Artikel können mehrere Preise haben – die Preisgruppe legt fest,
         * welcher Preis für den Kunden verwendet wird.
         */
        public ?int $priceGroup = null,
        /**
         * Archivierungsstatus.
         *
         * true = archiviert, false = aktiv.
         */
        public ?bool $archived = null,
        /**
         * Automatischer Mahnlauf.
         *
         * true = Kunde wird in automatischem Mahnlauf berücksichtigt.
         */
        public ?bool $dunningRun = null,
        /** Rabatt in Prozent. */
        public ?float $reduction = null,
        /**
         * Typ des Skonto-Satzes.
         *
         * Mögliche Werte: SETTINGS, ABSOLUTE, RELATIVE
         */
        public ?ValueType $discountRateType = null,
        /** Skonto-Satz. */
        public ?float $discountRate = null,
        /**
         * Typ des Skonto-Zeitraums.
         *
         * Mögliche Werte: SETTINGS, ABSOLUTE, RELATIVE
         */
        public ?ValueType $discountDaysType = null,
        /** Skonto-Tage. */
        public ?float $discountDays = null,
        /**
         * Typ der Fälligkeit.
         *
         * Mögliche Werte: SETTINGS, ABSOLUTE, RELATIVE
         */
        public ?ValueType $dueDaysType = null,
        /** Fälligkeit in Tagen nach Rechnungsdatum. */
        public ?int $dueDays = null,
        /**
         * Typ der Mahnfälligkeit.
         *
         * Mögliche Werte: SETTINGS, ABSOLUTE, RELATIVE
         */
        public ?ValueType $reminderDueDaysType = null,
        /** Mahnfälligkeit in Tagen. */
        public ?int $reminderDueDays = null,
        /**
         * Typ der Angebotsgültigkeit.
         *
         * Mögliche Werte: SETTINGS, ABSOLUTE, RELATIVE
         */
        public ?ValueType $offerValidityDaysType = null,
        /** Angebotsgültigkeit in Tagen. */
        public ?int $offerValidityDays = null,
        /** Kontoinhaber. */
        public ?string $bankAccountOwner = null,
        /** Bankleitzahl. */
        public ?string $bankNumber = null,
        /** Bankname. */
        public ?string $bankName = null,
        /** Kontonummer. */
        public ?string $bankAccountNumber = null,
        /** SWIFT / BIC. */
        public ?string $bankSwift = null,
        /** IBAN. */
        public ?string $bankIban = null,
        /** SEPA-Mandatsreferenz. */
        public ?string $sepaMandate = null,
        /** Ausstelldatum des SEPA-Mandats. */
        public ?DateTimeImmutable $sepaMandateDate = null,
        /**
         * Standard-Zahlarten.
         *
         * Billomat überträgt sie kommasepariert, z. B. "CASH,BANK_TRANSFER".
         */
        public array $defaultPaymentTypes = [],
        /** Kundenportal aktiviert? */
        public ?bool $enableCustomerportal = null,
        /** Brutto-Umsatz (read-only Auswertungsfeld). */
        public ?Money $revenueGross = null,
        /** Netto-Umsatz (read-only Auswertungsfeld). */
        public ?Money $revenueNet = null,
        /** Zeitpunkt der letzten Änderung. */
        public ?DateTimeImmutable $updated = null,
        /** Freies Zusatzfeld (siehe Custom-Meta-Attribute). */
        public ?string $customfield = null,
        /**
         * Kennung für E-Rechnungen (ZUGFeRD/Factur-X).
         *
         * Von Billomat geliefert, aber nicht dokumentiert.
         */
        public ?string $facturxIdentifier = null,
        /**
         * Verkäufer-Kennung für E-Rechnungen (ZUGFeRD/Factur-X).
         *
         * Von Billomat geliefert, aber nicht dokumentiert.
         */
        public ?string $sellerIdentifier = null,
        /** Von Billomat geliefert, aber nicht dokumentiert. */
        public ?bool $digExclude = null,
        /**
         * Felder, die Billomat liefert, das SDK aber (noch) nicht abbildet – roh, wie in der Response.
         *
         * Neue Billomat-Felder gehen so nicht verloren; toArray() gibt sie mit aus.
         */
        public array $extra = [],
    ) {
    }

    /**
     * Hydriert das Model aus einem Billomat-Response-Array.
     *
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: ScalarCaster::toIntOrNull($data['id'] ?? null),
            name: ScalarCaster::toStringOrNull($data['name'] ?? null) ?? '',
            created: ScalarCaster::toDateTimeOrNull($data['created'] ?? null),
            clientNumber: ScalarCaster::toStringOrNull($data['client_number'] ?? null),
            number: ScalarCaster::toIntOrNull($data['number'] ?? null),
            numberPre: ScalarCaster::toStringOrNull($data['number_pre'] ?? null),
            numberLength: ScalarCaster::toIntOrNull($data['number_length'] ?? null),
            street: ScalarCaster::toStringOrNull($data['street'] ?? null),
            zip: ScalarCaster::toStringOrNull($data['zip'] ?? null),
            city: ScalarCaster::toStringOrNull($data['city'] ?? null),
            state: ScalarCaster::toStringOrNull($data['state'] ?? null),
            countryCode: ScalarCaster::toStringOrNull($data['country_code'] ?? null),
            address: ScalarCaster::toStringOrNull($data['address'] ?? null),
            firstName: ScalarCaster::toStringOrNull($data['first_name'] ?? null),
            lastName: ScalarCaster::toStringOrNull($data['last_name'] ?? null),
            salutation: ScalarCaster::toStringOrNull($data['salutation'] ?? null),
            email: ScalarCaster::toStringOrNull($data['email'] ?? null),
            phone: ScalarCaster::toStringOrNull($data['phone'] ?? null),
            fax: ScalarCaster::toStringOrNull($data['fax'] ?? null),
            mobile: ScalarCaster::toStringOrNull($data['mobile'] ?? null),
            www: ScalarCaster::toStringOrNull($data['www'] ?? null),
            note: ScalarCaster::toStringOrNull($data['note'] ?? null),
            locale: ScalarCaster::toStringOrNull($data['locale'] ?? null),
            taxNumber: ScalarCaster::toStringOrNull($data['tax_number'] ?? null),
            vatNumber: ScalarCaster::toStringOrNull($data['vat_number'] ?? null),
            taxRule: TaxRule::fromApi(ScalarCaster::toStringOrNull($data['tax_rule'] ?? null)),
            netGross: NetGross::fromApi(ScalarCaster::toStringOrNull($data['net_gross'] ?? null)),
            currencyCode: ScalarCaster::toStringOrNull($data['currency_code'] ?? null),
            debitorAccountNumber: ScalarCaster::toIntOrNull($data['debitor_account_number'] ?? null),
            priceGroup: ScalarCaster::toIntOrNull($data['price_group'] ?? null),
            archived: ScalarCaster::toBoolOrNull($data['archived'] ?? null),
            dunningRun: ScalarCaster::toBoolOrNull($data['dunning_run'] ?? null),
            reduction: ScalarCaster::toFloatOrNull($data['reduction'] ?? null),
            discountRateType: ValueType::fromApi(ScalarCaster::toStringOrNull($data['discount_rate_type'] ?? null)),
            discountRate: ScalarCaster::toFloatOrNull($data['discount_rate'] ?? null),
            discountDaysType: ValueType::fromApi(ScalarCaster::toStringOrNull($data['discount_days_type'] ?? null)),
            discountDays: ScalarCaster::toFloatOrNull($data['discount_days'] ?? null),
            dueDaysType: ValueType::fromApi(ScalarCaster::toStringOrNull($data['due_days_type'] ?? null)),
            dueDays: ScalarCaster::toIntOrNull($data['due_days'] ?? null),
            reminderDueDaysType: ValueType::fromApi(ScalarCaster::toStringOrNull($data['reminder_due_days_type'] ?? null)),
            reminderDueDays: ScalarCaster::toIntOrNull($data['reminder_due_days'] ?? null),
            offerValidityDaysType: ValueType::fromApi(ScalarCaster::toStringOrNull($data['offer_validity_days_type'] ?? null)),
            offerValidityDays: ScalarCaster::toIntOrNull($data['offer_validity_days'] ?? null),
            bankAccountOwner: ScalarCaster::toStringOrNull($data['bank_account_owner'] ?? null),
            bankNumber: ScalarCaster::toStringOrNull($data['bank_number'] ?? null),
            bankName: ScalarCaster::toStringOrNull($data['bank_name'] ?? null),
            bankAccountNumber: ScalarCaster::toStringOrNull($data['bank_account_number'] ?? null),
            bankSwift: ScalarCaster::toStringOrNull($data['bank_swift'] ?? null),
            bankIban: ScalarCaster::toStringOrNull($data['bank_iban'] ?? null),
            sepaMandate: ScalarCaster::toStringOrNull($data['sepa_mandate'] ?? null),
            sepaMandateDate: ScalarCaster::toDateTimeOrNull($data['sepa_mandate_date'] ?? null),
            defaultPaymentTypes: ListCaster::toEnumList($data['default_payment_types'] ?? null, PaymentType::class),
            enableCustomerportal: ScalarCaster::toBoolOrNull($data['enable_customerportal'] ?? null),
            revenueGross: ScalarCaster::toMoneyOrNull($data['revenue_gross'] ?? null),
            revenueNet: ScalarCaster::toMoneyOrNull($data['revenue_net'] ?? null),
            updated: ScalarCaster::toDateTimeOrNull($data['updated'] ?? null),
            customfield: ScalarCaster::toStringOrNull($data['customfield'] ?? null),
            facturxIdentifier: ScalarCaster::toStringOrNull($data['facturx_identifier'] ?? null),
            sellerIdentifier: ScalarCaster::toStringOrNull($data['seller_identifier'] ?? null),
            digExclude: ScalarCaster::toBoolOrNull($data['dig_exclude'] ?? null),
            extra: array_diff_key($data, self::FIELDS),
        );
    }

    /**
     * Exportiert das Model als Array mit Billomat-Feldnamen.
     *
     * Praktisch für Debugging oder Log-Ausgaben.
     *
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            ...$this->extra,
            'id' => $this->id,
            'name' => $this->name,
            'created' => $this->created?->format(DATE_ATOM),
            'client_number' => $this->clientNumber,
            'number' => $this->number,
            'number_pre' => $this->numberPre,
            'number_length' => $this->numberLength,
            'street' => $this->street,
            'zip' => $this->zip,
            'city' => $this->city,
            'state' => $this->state,
            'country_code' => $this->countryCode,
            'address' => $this->address,
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
            'sepa_mandate_date' => $this->sepaMandateDate?->format('Y-m-d'),
            'default_payment_types' => [] === $this->defaultPaymentTypes ? null : implode(',', array_map(static fn (PaymentType $v): string => $v->value, $this->defaultPaymentTypes)),
            'enable_customerportal' => $this->enableCustomerportal,
            'revenue_gross' => $this->revenueGross?->toString(),
            'revenue_net' => $this->revenueNet?->toString(),
            'updated' => $this->updated?->format(DATE_ATOM),
            'customfield' => $this->customfield,
            'facturx_identifier' => $this->facturxIdentifier,
            'seller_identifier' => $this->sellerIdentifier,
            'dig_exclude' => $this->digExclude,
        ];
    }
}
