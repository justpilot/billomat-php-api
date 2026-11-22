<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use DateTimeImmutable;
use Justpilot\Billomat\Internal\ScalarCaster;

use const DATE_ATOM;

/**
 * Lieferadresse (Client Delivery Address) eines Kunden.
 *
 * Dieses Model bildet die Struktur von GET /client-delivery-addresses bzw. GET /client-delivery-addresses/{id} ab.
 * Es ist als Read-Model gedacht: Die Erstellung erfolgt über ClientDeliveryAddressCreateOptions.
 *
 * Dokumentation:
 * https://www.billomat.com/api/lieferadressen/
 *
 * Generiert aus tools/codegen/definitions/client-delivery-addresses.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 *
 * @experimental Billomat stellt die Lieferadressen noch fertig; laut Doku gelten die Endpunkte erst ab dem 01.01.2027 verbindlich. Bis dahin kann sich die Klasse auch in einem Minor-Release ändern (siehe docs/concepts/backward-compatibility.md).
 */
final readonly class ClientDeliveryAddress
{
    /** Billomat-Felder, die das Model abbildet. */
    private const array FIELDS = [
        'id' => true,
        'client_id' => true,
        'created' => true,
        'updated' => true,
        'label' => true,
        'name' => true,
        'salutation' => true,
        'first_name' => true,
        'last_name' => true,
        'street' => true,
        'address_line_2' => true,
        'address_line_3' => true,
        'zip' => true,
        'city' => true,
        'state' => true,
        'country_code' => true,
        'is_default' => true,
    ];

    /** @param array<string, mixed> $extra */
    public function __construct(
        /** Interne Billomat-ID der Lieferadresse. */
        public int $id,
        /** ID des Kunden, zu dem die Lieferadresse gehört. */
        public int $clientId,
        /** Erstellungszeitpunkt der Lieferadresse. */
        public ?DateTimeImmutable $created = null,
        /** Zeitpunkt der letzten Änderung. */
        public ?DateTimeImmutable $updated = null,
        /**
         * Bezeichnung der Lieferadresse.
         *
         * Beispiel: "Lager Berlin"
         */
        public ?string $label = null,
        /** Firmenname. */
        public ?string $name = null,
        /** Anrede. */
        public ?string $salutation = null,
        /** Vorname. */
        public ?string $firstName = null,
        /** Nachname. */
        public ?string $lastName = null,
        /** Straße. */
        public ?string $street = null,
        /** Adresszusatz (Zeile 2). */
        public ?string $addressLine2 = null,
        /** Adresszusatz (Zeile 3). */
        public ?string $addressLine3 = null,
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
        /** Standard-Lieferadresse des Kunden? */
        public ?bool $isDefault = null,
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
            clientId: ScalarCaster::required(ScalarCaster::toIntOrNull($data['client_id'] ?? null), self::class, 'client_id'),
            created: ScalarCaster::toDateTimeOrNull($data['created'] ?? null),
            updated: ScalarCaster::toDateTimeOrNull($data['updated'] ?? null),
            label: ScalarCaster::toStringOrNull($data['label'] ?? null),
            name: ScalarCaster::toStringOrNull($data['name'] ?? null),
            salutation: ScalarCaster::toStringOrNull($data['salutation'] ?? null),
            firstName: ScalarCaster::toStringOrNull($data['first_name'] ?? null),
            lastName: ScalarCaster::toStringOrNull($data['last_name'] ?? null),
            street: ScalarCaster::toStringOrNull($data['street'] ?? null),
            addressLine2: ScalarCaster::toStringOrNull($data['address_line_2'] ?? null),
            addressLine3: ScalarCaster::toStringOrNull($data['address_line_3'] ?? null),
            zip: ScalarCaster::toStringOrNull($data['zip'] ?? null),
            city: ScalarCaster::toStringOrNull($data['city'] ?? null),
            state: ScalarCaster::toStringOrNull($data['state'] ?? null),
            countryCode: ScalarCaster::toStringOrNull($data['country_code'] ?? null),
            isDefault: ScalarCaster::toBoolOrNull($data['is_default'] ?? null),
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
            'updated' => $this->updated?->format(DATE_ATOM),
            'label' => $this->label,
            'name' => $this->name,
            'salutation' => $this->salutation,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'street' => $this->street,
            'address_line_2' => $this->addressLine2,
            'address_line_3' => $this->addressLine3,
            'zip' => $this->zip,
            'city' => $this->city,
            'state' => $this->state,
            'country_code' => $this->countryCode,
            'is_default' => $this->isDefault,
        ];
    }
}
