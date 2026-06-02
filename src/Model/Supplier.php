<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use DateTimeImmutable;
use Justpilot\Billomat\Internal\ScalarCaster;

use const DATE_ATOM;

/**
 * Lieferant (Supplier) aus der Billomat-API.
 *
 * Doku: https://www.billomat.com/en/api/suppliers/
 */
final readonly class Supplier
{
    public function __construct(
        public ?int $id,
        public ?DateTimeImmutable $created,
        public string $name,
        public ?int $clientNumber = null,
        public ?string $salutation = null,
        public ?string $firstName = null,
        public ?string $lastName = null,
        public ?string $street = null,
        public ?string $zip = null,
        public ?string $city = null,
        public ?string $state = null,
        public ?string $countryCode = null,
        public ?string $address = null,
        public ?string $note = null,
        public ?string $email = null,
        public ?string $phone = null,
        public ?string $fax = null,
        public ?string $mobile = null,
        public ?string $www = null,
        public ?string $taxNumber = null,
        public ?string $vatNumber = null,
        public ?string $bankAccountNumber = null,
        public ?string $bankAccountOwner = null,
        public ?string $bankNumber = null,
        public ?string $bankName = null,
        public ?string $bankIban = null,
        public ?string $bankBic = null,
        public ?string $currencyCode = null,
    ) {
    }

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: ScalarCaster::toIntOrNull($data['id'] ?? null),
            created: ScalarCaster::toDateTimeOrNull($data['created'] ?? null),
            name: (string) ($data['name'] ?? ''),
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
            bankBic: ScalarCaster::toStringOrNull($data['bank_bic'] ?? null),
            currencyCode: ScalarCaster::toStringOrNull($data['currency_code'] ?? null),
        );
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
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
        ];
    }
}
