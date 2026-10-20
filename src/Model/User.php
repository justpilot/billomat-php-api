<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use DateTimeImmutable;
use Justpilot\Billomat\Internal\ScalarCaster;

use const DATE_ATOM;

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
        /**
         * @deprecated since 3.1 – Billomat liefert `phone` nicht (mehr) aus (Stand 2026-10), der Wert ist immer `null`. Wird in 4.0 entfernt.
         */
        public ?string $phone = null,
        /**
         * @deprecated since 3.1 – Billomat liefert `mobile` nicht (mehr) aus (Stand 2026-10), der Wert ist immer `null`. Wird in 4.0 entfernt.
         */
        public ?string $mobile = null,
        /**
         * @deprecated since 3.1 – Billomat liefert `fax` nicht (mehr) aus (Stand 2026-10), der Wert ist immer `null`. Wird in 4.0 entfernt.
         */
        public ?string $fax = null,
        public ?int $roleId = null,
        public ?DateTimeImmutable $created = null,
        public ?DateTimeImmutable $updated = null,
        public ?string $customfield = null,
        public ?string $locale = null,
        public ?string $timezone = null,
        public ?bool $isAccountOwner = null,
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
            created: ScalarCaster::toDateTimeOrNull($data['created'] ?? null),
            updated: ScalarCaster::toDateTimeOrNull($data['updated'] ?? null),
            customfield: ScalarCaster::toStringOrNull($data['customfield'] ?? null),
            locale: ScalarCaster::toStringOrNull($data['locale'] ?? null),
            timezone: ScalarCaster::toStringOrNull($data['timezone'] ?? null),
            isAccountOwner: ScalarCaster::toBoolOrNull($data['is_account_owner'] ?? null),
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
            'created' => $this->created?->format(DATE_ATOM),
            'updated' => $this->updated?->format(DATE_ATOM),
            'customfield' => $this->customfield,
            'locale' => $this->locale,
            'timezone' => $this->timezone,
            'is_account_owner' => $this->isAccountOwner,
        ];
    }
}
