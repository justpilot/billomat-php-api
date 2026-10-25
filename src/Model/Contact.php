<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use DateTimeImmutable;
use Justpilot\Billomat\Internal\ScalarCaster;

use const DATE_ATOM;

/**
 * Ansprechpartner (Contact) eines Kunden.
 *
 * Dieses Model bildet die Struktur von GET /contacts bzw. GET /contacts/{id} ab.
 * Es ist als Read-Model gedacht: Die Erstellung erfolgt über ContactCreateOptions.
 *
 * Dokumentation:
 * https://www.billomat.com/en/api/clients/contacts/
 *
 * Generiert aus tools/codegen/definitions/contacts.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final readonly class Contact
{
    /** Billomat-Felder, die das Model abbildet. */
    private const array FIELDS = [
        'id' => true,
        'client_id' => true,
        'created' => true,
        'label' => true,
        'salutation' => true,
        'first_name' => true,
        'last_name' => true,
        'street' => true,
        'zip' => true,
        'city' => true,
        'state' => true,
        'country_code' => true,
        'email' => true,
        'phone' => true,
        'fax' => true,
        'mobile' => true,
        'updated' => true,
        'customfield' => true,
        'name' => true,
        'address' => true,
        'www' => true,
        'locale' => true,
    ];

    /** @param array<string, mixed> $extra */
    public function __construct(
        /** Interne Billomat-ID des Kontakts. */
        public int $id,
        /** ID des Kunden, zu dem der Kontakt gehört. */
        public int $clientId = 0,
        /** Erstellungszeitpunkt des Kontakts. */
        public ?DateTimeImmutable $created = null,
        /** Bezeichnung des Kontakts. */
        public ?string $label = null,
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
        /** E-Mail-Adresse. */
        public ?string $email = null,
        /** Telefonnummer. */
        public ?string $phone = null,
        /** Faxnummer. */
        public ?string $fax = null,
        /** Mobilnummer. */
        public ?string $mobile = null,
        /** Zeitpunkt der letzten Änderung. */
        public ?DateTimeImmutable $updated = null,
        /** Freies Zusatzfeld (siehe Custom-Meta-Attribute). */
        public ?string $customfield = null,
        /** Firmenname. */
        public ?string $name = null,
        /** Von Billomat zusammengesetzte Adresse (mehrzeilig). */
        public ?string $address = null,
        /**
         * Website (ohne Schema).
         *
         * Beispiel: "example.com"
         */
        public ?string $www = null,
        /**
         * Gebietsschema des Kontakts.
         *
         * Beispiel: "de_DE"
         */
        public ?string $locale = null,
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
            id: ScalarCaster::required(ScalarCaster::toIntOrNull($data['id'] ?? null), self::class, 'id'),
            clientId: ScalarCaster::toIntOrNull($data['client_id'] ?? null) ?? 0,
            created: ScalarCaster::toDateTimeOrNull($data['created'] ?? null),
            label: ScalarCaster::toStringOrNull($data['label'] ?? null),
            salutation: ScalarCaster::toStringOrNull($data['salutation'] ?? null),
            firstName: ScalarCaster::toStringOrNull($data['first_name'] ?? null),
            lastName: ScalarCaster::toStringOrNull($data['last_name'] ?? null),
            street: ScalarCaster::toStringOrNull($data['street'] ?? null),
            zip: ScalarCaster::toStringOrNull($data['zip'] ?? null),
            city: ScalarCaster::toStringOrNull($data['city'] ?? null),
            state: ScalarCaster::toStringOrNull($data['state'] ?? null),
            countryCode: ScalarCaster::toStringOrNull($data['country_code'] ?? null),
            email: ScalarCaster::toStringOrNull($data['email'] ?? null),
            phone: ScalarCaster::toStringOrNull($data['phone'] ?? null),
            fax: ScalarCaster::toStringOrNull($data['fax'] ?? null),
            mobile: ScalarCaster::toStringOrNull($data['mobile'] ?? null),
            updated: ScalarCaster::toDateTimeOrNull($data['updated'] ?? null),
            customfield: ScalarCaster::toStringOrNull($data['customfield'] ?? null),
            name: ScalarCaster::toStringOrNull($data['name'] ?? null),
            address: ScalarCaster::toStringOrNull($data['address'] ?? null),
            www: ScalarCaster::toStringOrNull($data['www'] ?? null),
            locale: ScalarCaster::toStringOrNull($data['locale'] ?? null),
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
            'client_id' => $this->clientId,
            'created' => $this->created?->format(DATE_ATOM),
            'label' => $this->label,
            'salutation' => $this->salutation,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'street' => $this->street,
            'zip' => $this->zip,
            'city' => $this->city,
            'state' => $this->state,
            'country_code' => $this->countryCode,
            'email' => $this->email,
            'phone' => $this->phone,
            'fax' => $this->fax,
            'mobile' => $this->mobile,
            'updated' => $this->updated?->format(DATE_ATOM),
            'customfield' => $this->customfield,
            'name' => $this->name,
            'address' => $this->address,
            'www' => $this->www,
            'locale' => $this->locale,
        ];
    }
}
