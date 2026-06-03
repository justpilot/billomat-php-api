<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

/**
 * Typisierter Payload für PUT /suppliers/{id}.
 */
final class SupplierUpdateOptions
{
    public ?string $name = null;

    public ?string $salutation = null;

    public ?string $firstName = null;

    public ?string $lastName = null;

    public ?string $street = null;

    public ?string $zip = null;

    public ?string $city = null;

    public ?string $state = null;

    public ?string $countryCode = null;

    public ?string $note = null;

    public ?string $email = null;

    public ?string $phone = null;

    public ?string $fax = null;

    public ?string $mobile = null;

    public ?string $www = null;

    public ?string $taxNumber = null;

    public ?string $vatNumber = null;

    public ?string $bankAccountNumber = null;

    public ?string $bankAccountOwner = null;

    public ?string $bankNumber = null;

    public ?string $bankName = null;

    public ?string $bankIban = null;

    public ?string $bankBic = null;

    public ?string $bankSwift = null;

    public ?string $clientNumber = null;

    public ?string $creditorIdentifier = null;

    public ?string $currencyCode = null;

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        $data = [
            'name' => $this->name,
            'salutation' => $this->salutation,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'street' => $this->street,
            'zip' => $this->zip,
            'city' => $this->city,
            'state' => $this->state,
            'country_code' => $this->countryCode,
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
            'bank_swift' => $this->bankSwift,
            'client_number' => $this->clientNumber,
            'creditor_identifier' => $this->creditorIdentifier,
            'currency_code' => $this->currencyCode,
        ];

        return array_filter($data, static fn (int|string|float|null $v): bool => null !== $v);
    }
}
