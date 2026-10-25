<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use DateTimeImmutable;
use Justpilot\Billomat\Internal\ListCaster;
use Justpilot\Billomat\Internal\ScalarCaster;

use const DATE_ATOM;

/**
 * Lieferant (Supplier) aus der Billomat-API.
 *
 * Dieses Model bildet die Struktur von GET /suppliers bzw. GET /suppliers/{id} ab.
 * Es ist als Read-Model gedacht: Die Erstellung erfolgt über SupplierCreateOptions.
 *
 * Dokumentation:
 * https://www.billomat.com/en/api/suppliers/
 *
 * Generiert aus tools/codegen/definitions/suppliers.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final readonly class Supplier
{
    /** Billomat-Felder, die das Model abbildet. */
    private const array FIELDS = [
        'id' => true,
        'created' => true,
        'name' => true,
        'client_number' => true,
        'salutation' => true,
        'first_name' => true,
        'last_name' => true,
        'street' => true,
        'zip' => true,
        'city' => true,
        'state' => true,
        'country_code' => true,
        'address' => true,
        'note' => true,
        'email' => true,
        'phone' => true,
        'fax' => true,
        'mobile' => true,
        'www' => true,
        'tax_number' => true,
        'vat_number' => true,
        'bank_account_number' => true,
        'bank_account_owner' => true,
        'bank_number' => true,
        'bank_name' => true,
        'bank_iban' => true,
        'bank_swift' => true,
        'bank_bic' => true,
        'currency_code' => true,
        'updated' => true,
        'customfield' => true,
        'is_eu_country' => true,
        'locale' => true,
        'creditor_account_number' => true,
        'creditor_identifier' => true,
        'costs_gross' => true,
        'costs_net' => true,
        'supplier-property-values' => true,
    ];

    /**
     * @param list<SupplierPropertyValue> $propertyValues
     * @param array<string, mixed>        $extra
     */
    public function __construct(
        /** Interne Billomat-ID des Lieferanten. */
        public ?int $id,
        /** Erstellungszeitpunkt des Lieferanten. */
        public ?DateTimeImmutable $created,
        /** Firmenname des Lieferanten. */
        public string $name,
        /** Eigene Kundennummer beim Lieferanten. */
        public ?int $clientNumber = null,
        /** Anrede. */
        public ?string $salutation = null,
        /** Vorname. */
        public ?string $firstName = null,
        /** Nachname. */
        public ?string $lastName = null,
        /** Straße. */
        public ?string $street = null,
        /** Postleitzahl. */
        public ?string $zip = null,
        /** Ort. */
        public ?string $city = null,
        /** Bundesland / Bezirk / Region. */
        public ?string $state = null,
        /**
         * Ländercode (ISO 3166-1 Alpha-2).
         *
         * Beispiel: "DE", "AT", "CH"
         */
        public ?string $countryCode = null,
        /** Von Billomat zusammengesetzte Adresse (mehrzeilig). */
        public ?string $address = null,
        /** Notiz zum Lieferanten. */
        public ?string $note = null,
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
        /** Steuernummer. */
        public ?string $taxNumber = null,
        /** Umsatzsteuer-ID (USt-IdNr.). */
        public ?string $vatNumber = null,
        /** Kontonummer. */
        public ?string $bankAccountNumber = null,
        /** Kontoinhaber. */
        public ?string $bankAccountOwner = null,
        /** Bankleitzahl. */
        public ?string $bankNumber = null,
        /** Bankname. */
        public ?string $bankName = null,
        /** IBAN. */
        public ?string $bankIban = null,
        /**
         * SWIFT / BIC.
         *
         * Billomat liefert den BIC als `bank_swift`; `bank_bic` dient nur als Fallback.
         */
        public ?string $bankBic = null,
        /**
         * Währung des Lieferanten (ISO-Währungscode).
         *
         * Leer = Account-Währung.
         */
        public ?string $currencyCode = null,
        /** Zeitpunkt der letzten Änderung. */
        public ?DateTimeImmutable $updated = null,
        /** Freies Zusatzfeld (siehe Custom-Meta-Attribute). */
        public ?string $customfield = null,
        /** Sitzt der Lieferant in einem EU-Land? */
        public ?bool $isEuCountry = null,
        /**
         * Gebietsschema des Lieferanten.
         *
         * Beispiel: "de_DE"
         */
        public ?string $locale = null,
        /** Kreditorennummer. */
        public ?string $creditorAccountNumber = null,
        /** SEPA-Gläubiger-ID. */
        public ?string $creditorIdentifier = null,
        /** Brutto-Kosten (read-only Auswertungsfeld). */
        public ?Money $costsGross = null,
        /** Netto-Kosten (read-only Auswertungsfeld). */
        public ?Money $costsNet = null,
        /**
         * Attributwerte des Lieferanten.
         *
         * Von Billomat geliefert, aber nicht dokumentiert (leer: "").
         */
        public array $propertyValues = [],
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
            created: ScalarCaster::toDateTimeOrNull($data['created'] ?? null),
            name: ScalarCaster::toStringOrNull($data['name'] ?? null) ?? '',
            clientNumber: ScalarCaster::toIntOrNull($data['client_number'] ?? null),
            salutation: ScalarCaster::toStringOrNull($data['salutation'] ?? null),
            firstName: ScalarCaster::toStringOrNull($data['first_name'] ?? null),
            lastName: ScalarCaster::toStringOrNull($data['last_name'] ?? null),
            street: ScalarCaster::toStringOrNull($data['street'] ?? null),
            zip: ScalarCaster::toStringOrNull($data['zip'] ?? null),
            city: ScalarCaster::toStringOrNull($data['city'] ?? null),
            state: ScalarCaster::toStringOrNull($data['state'] ?? null),
            countryCode: ScalarCaster::toStringOrNull($data['country_code'] ?? null),
            address: ScalarCaster::toStringOrNull($data['address'] ?? null),
            note: ScalarCaster::toStringOrNull($data['note'] ?? null),
            email: ScalarCaster::toStringOrNull($data['email'] ?? null),
            phone: ScalarCaster::toStringOrNull($data['phone'] ?? null),
            fax: ScalarCaster::toStringOrNull($data['fax'] ?? null),
            mobile: ScalarCaster::toStringOrNull($data['mobile'] ?? null),
            www: ScalarCaster::toStringOrNull($data['www'] ?? null),
            taxNumber: ScalarCaster::toStringOrNull($data['tax_number'] ?? null),
            vatNumber: ScalarCaster::toStringOrNull($data['vat_number'] ?? null),
            bankAccountNumber: ScalarCaster::toStringOrNull($data['bank_account_number'] ?? null),
            bankAccountOwner: ScalarCaster::toStringOrNull($data['bank_account_owner'] ?? null),
            bankNumber: ScalarCaster::toStringOrNull($data['bank_number'] ?? null),
            bankName: ScalarCaster::toStringOrNull($data['bank_name'] ?? null),
            bankIban: ScalarCaster::toStringOrNull($data['bank_iban'] ?? null),
            bankBic: ScalarCaster::toStringOrNull($data['bank_swift'] ?? $data['bank_bic'] ?? null),
            currencyCode: ScalarCaster::toStringOrNull($data['currency_code'] ?? null),
            updated: ScalarCaster::toDateTimeOrNull($data['updated'] ?? null),
            customfield: ScalarCaster::toStringOrNull($data['customfield'] ?? null),
            isEuCountry: ScalarCaster::toBoolOrNull($data['is_eu_country'] ?? null),
            locale: ScalarCaster::toStringOrNull($data['locale'] ?? null),
            creditorAccountNumber: ScalarCaster::toStringOrNull($data['creditor_account_number'] ?? null),
            creditorIdentifier: ScalarCaster::toStringOrNull($data['creditor_identifier'] ?? null),
            costsGross: ScalarCaster::toMoneyOrNull($data['costs_gross'] ?? null),
            costsNet: ScalarCaster::toMoneyOrNull($data['costs_net'] ?? null),
            propertyValues: ListCaster::toNestedList($data['supplier-property-values'] ?? null, 'supplier-property-value', 'id', SupplierPropertyValue::fromArray(...)),
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
        $data = [
            ...$this->extra,
            'id' => $this->id,
            'created' => $this->created?->format(DATE_ATOM),
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
            'address' => $this->address,
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
            'currency_code' => $this->currencyCode,
            'updated' => $this->updated?->format(DATE_ATOM),
            'customfield' => $this->customfield,
            'is_eu_country' => $this->isEuCountry,
            'locale' => $this->locale,
            'creditor_account_number' => $this->creditorAccountNumber,
            'creditor_identifier' => $this->creditorIdentifier,
            'costs_gross' => $this->costsGross?->toString(),
            'costs_net' => $this->costsNet?->toString(),
        ];

        if ([] !== $this->propertyValues) {
            $data['supplier-property-values'] = [
                'supplier-property-value' => array_map(static fn (SupplierPropertyValue $item): array => $item->toArray(), $this->propertyValues),
            ];
        }

        return $data;
    }
}
