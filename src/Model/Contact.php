<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use DateTimeImmutable;
use Throwable;

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
        $created = null;
        if (!empty($data['created'])) {
            try {
                $created = new DateTimeImmutable((string) $data['created']);
            } catch (Throwable) {
                $created = null;
            }
        }

        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            clientId: (int) ($data['client_id'] ?? 0),
            created: $created,
            label: $data['label'] ?? null,
            salutation: $data['salutation'] ?? null,
            firstName: $data['first_name'] ?? null,
            lastName: $data['last_name'] ?? null,
            street: $data['street'] ?? null,
            zip: $data['zip'] ?? null,
            city: $data['city'] ?? null,
            state: $data['state'] ?? null,
            countryCode: $data['country_code'] ?? null,
            email: $data['email'] ?? null,
            phone: $data['phone'] ?? null,
            fax: $data['fax'] ?? null,
            mobile: $data['mobile'] ?? null,
            note: $data['note'] ?? null,
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
