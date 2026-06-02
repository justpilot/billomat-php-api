<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use Justpilot\Billomat\Internal\ScalarCaster;

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
            id: ScalarCaster::toIntOrNull($data['id'] ?? null),
            email: ScalarCaster::toStringOrNull($data['email'] ?? null),
            firstName: ScalarCaster::toStringOrNull($data['first_name'] ?? null),
            lastName: ScalarCaster::toStringOrNull($data['last_name'] ?? null),
            salutation: ScalarCaster::toStringOrNull($data['salutation'] ?? null),
            phone: ScalarCaster::toStringOrNull($data['phone'] ?? null),
            mobile: ScalarCaster::toStringOrNull($data['mobile'] ?? null),
            fax: ScalarCaster::toStringOrNull($data['fax'] ?? null),
            roleId: ScalarCaster::toIntOrNull($data['role_id'] ?? null),
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
