<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

final readonly class Client
{
    public function __construct(
        public ?int    $id,
        public string  $name,
        public ?string $clientNumber = null,
        public ?string $email = null,
        public ?string $street = null,
        public ?string $zip = null,
        public ?string $city = null,
        public ?string $country = null,
    )
    {
    }

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int)$data['id'] : null,
            name: (string)($data['name'] ?? ''),
            clientNumber: $data['client_number'] ?? null,
            email: $data['email'] ?? null,
            street: $data['street'] ?? null,
            zip: $data['zip'] ?? null,
            city: $data['city'] ?? null,
            country: $data['country'] ?? null,
        );
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'client_number' => $this->clientNumber,
            'email' => $this->email,
            'street' => $this->street,
            'zip' => $this->zip,
            'city' => $this->city,
            'country' => $this->country,
        ];
    }

    /**
     * Payload für POST /clients (id wird nicht gesendet).
     *
     * @return array<string,mixed>
     */
    public function toArrayForCreate(): array
    {
        return [
            'name' => $this->name,
            'client_number' => $this->clientNumber,
            'email' => $this->email,
            'street' => $this->street,
            'zip' => $this->zip,
            'city' => $this->city,
            'country' => $this->country,
        ];
    }
}