<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

/**
 * Typisierter Payload für POST /contacts.
 *
 * Doku: https://www.billomat.com/en/api/clients/contacts/
 */
final class ContactCreateOptions
{
    public function __construct(
        /** ID des Kunden (Pflichtfeld). */
        public int $clientId,
    ) {
    }

    public ?string $label = null;

    public ?string $salutation = null;

    public ?string $firstName = null;

    public ?string $lastName = null;

    public ?string $street = null;

    public ?string $zip = null;

    public ?string $city = null;

    public ?string $state = null;

    public ?string $countryCode = null;

    public ?string $email = null;

    public ?string $phone = null;

    public ?string $fax = null;

    public ?string $mobile = null;

    public ?string $note = null;

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        $data = [
            'client_id' => $this->clientId,
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

        return array_filter($data, static fn (int|string|null $v): bool => null !== $v);
    }
}
