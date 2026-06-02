<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use DateTimeImmutable;
use Justpilot\Billomat\Internal\ScalarCaster;

use const DATE_ATOM;

/**
 * Ansprechpartner (Contact) eines Kunden.
 *
 * Doku: https://www.billomat.com/en/api/clients/contacts/
 */
final readonly class Contact
{
    public function __construct(
        public ?int $id,
        public int $clientId,
        public ?DateTimeImmutable $created = null,
        public ?string $label = null,
        public ?string $salutation = null,
        public ?string $firstName = null,
        public ?string $lastName = null,
        public ?string $street = null,
        public ?string $zip = null,
        public ?string $city = null,
        public ?string $state = null,
        public ?string $countryCode = null,
        public ?string $email = null,
        public ?string $phone = null,
        public ?string $fax = null,
        public ?string $mobile = null,
        public ?string $note = null,
    ) {
    }

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: ScalarCaster::toIntOrNull($data['id'] ?? null),
            clientId: (int) ($data['client_id'] ?? 0),
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
            note: ScalarCaster::toStringOrNull($data['note'] ?? null),
        );
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
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
            'note' => $this->note,
        ];
    }
}
