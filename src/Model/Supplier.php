<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use DateTimeImmutable;
use Throwable;

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
            created: $created,
            name: (string) ($data['name'] ?? ''),
            clientNumber: isset($data['client_number']) && '' !== $data['client_number']
                ? (int) $data['client_number']
                : null,
            salutation: $data['salutation'] ?? null,
            firstName: $data['first_name'] ?? null,
            lastName: $data['last_name'] ?? null,
            street: $data['street'] ?? null,
            zip: $data['zip'] ?? null,
            city: $data['city'] ?? null,
            state: $data['state'] ?? null,
            countryCode: $data['country_code'] ?? null,
            address: $data['address'] ?? null,
            note: $data['note'] ?? null,
            email: $data['email'] ?? null,
            phone: $data['phone'] ?? null,
            fax: $data['fax'] ?? null,
            mobile: $data['mobile'] ?? null,
            www: $data['www'] ?? null,
            taxNumber: $data['tax_number'] ?? null,
            vatNumber: $data['vat_number'] ?? null,
            bankAccountNumber: $data['bank_account_number'] ?? null,
            bankAccountOwner: $data['bank_account_owner'] ?? null,
            bankNumber: $data['bank_number'] ?? null,
            bankName: $data['bank_name'] ?? null,
            bankIban: $data['bank_iban'] ?? null,
            bankBic: $data['bank_bic'] ?? null,
            currencyCode: $data['currency_code'] ?? null,
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
