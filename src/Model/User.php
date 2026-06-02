<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

/**
 * Benutzer/Mitarbeiter aus der Billomat-API.
 *
 * Doku: https://www.billomat.com/en/api/users/
 */
final readonly class User
{
    public function __construct(
        public ?int $id,
        public ?string $email,
        public ?string $firstName = null,
        public ?string $lastName = null,
        public ?string $salutation = null,
        public ?string $phone = null,
        public ?string $mobile = null,
        public ?string $fax = null,
        public ?int $roleId = null,
    ) {
    }

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            email: $data['email'] ?? null,
            firstName: $data['first_name'] ?? null,
            lastName: $data['last_name'] ?? null,
            salutation: $data['salutation'] ?? null,
            phone: $data['phone'] ?? null,
            mobile: $data['mobile'] ?? null,
            fax: $data['fax'] ?? null,
            roleId: isset($data['role_id']) && '' !== $data['role_id']
                ? (int) $data['role_id']
                : null,
        );
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'salutation' => $this->salutation,
            'phone' => $this->phone,
            'mobile' => $this->mobile,
            'fax' => $this->fax,
            'role_id' => $this->roleId,
        ];
    }
}
