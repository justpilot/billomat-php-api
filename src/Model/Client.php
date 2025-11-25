<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

final readonly class Client
{
    /** Interne Billomat-ID (wird bei neuen Clients nicht gesetzt) */
    public ?int $id;

    /** Firmenname */
    public string $name;

    /** Vorname */
    public ?string $firstName;

    /** Nachname */
    public ?string $lastName;

    /** Anrede */
    public ?string $salutation;

    /** Kundennummer */
    public ?string $clientNumber;

    /** E-Mail-Adresse */
    public ?string $email;

    /** Telefon */
    public ?string $phone;

    /** Straße */
    public ?string $street;

    /** PLZ */
    public ?string $zip;

    /** Ort */
    public ?string $city;

    /** ISO 3166-1 Alpha-2 Ländercode */
    public ?string $country;

    /** Debitorennummer */
    public ?int $debitorAccountNumber;

    /**
     * @param int|null $id Billomat-ID
     * @param string $name Firmenname
     * @param string|null $firstName Vorname
     * @param string|null $lastName Nachname
     * @param string|null $salutation Anrede
     * @param string|null $clientNumber Kundennummer
     * @param string|null $email E-Mail-Adresse
     * @param string|null $phone Telefon
     * @param string|null $street Straße
     * @param string|null $zip PLZ
     * @param string|null $city Ort
     * @param string|null $country ISO-Ländercode (DE, AT, CH, ...)
     * @param ?int $debitorAccountNumber Debitorennummer
     */
    public function __construct(
        ?int    $id,
        string  $name,
        ?string $firstName = null,
        ?string $lastName = null,
        ?string $salutation = null,
        ?string $clientNumber = null,
        ?string $email = null,
        ?string $phone = null,
        ?string $street = null,
        ?string $zip = null,
        ?string $city = null,
        ?string $country = null,
        ?int    $debitorAccountNumber = null
    )
    {
        $this->id = $id;
        $this->name = $name;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->salutation = $salutation;
        $this->clientNumber = $clientNumber;
        $this->email = $email;
        $this->phone = $phone;
        $this->street = $street;
        $this->zip = $zip;
        $this->city = $city;
        $this->country = $country;
        $this->debitorAccountNumber = $debitorAccountNumber;
    }

    /**
     * Hydriert einen Client aus einem Billomat-Response-Array.
     *
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int)$data['id'] : null,
            name: (string)($data['name'] ?? ''),
            firstName: $data['first_name'] ?? null,
            lastName: $data['last_name'] ?? null,
            salutation: $data['salutation'] ?? null,
            clientNumber: $data['client_number'] ?? null,
            email: $data['email'] ?? null,
            phone: $data['phone'] ?? null,
            street: $data['street'] ?? null,
            zip: $data['zip'] ?? null,
            city: $data['city'] ?? null,
            country: $data['country'] ?? null,
            debitorAccountNumber: isset($data['debitor_account_number']) ? (int)$data['debitor_account_number'] : null
        );
    }

    /**
     * Exportiert den vollständigen Client als Array.
     *
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'salutation' => $this->salutation,
            'client_number' => $this->clientNumber,
            'email' => $this->email,
            'street' => $this->street,
            'zip' => $this->zip,
            'city' => $this->city,
            'country' => $this->country,
            'debitor_account_number' => $this->debitorAccountNumber,
        ];
    }

    /**
     * Payload zum Erstellen eines neuen Clients.
     *
     * @return array<string,mixed>
     */
    public function toArrayForCreate(): array
    {
        return [
            'name' => $this->name,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'salutation' => $this->salutation,
            'client_number' => $this->clientNumber,
            'email' => $this->email,
            'phone' => $this->phone,
            'street' => $this->street,
            'zip' => $this->zip,
            'city' => $this->city,
            'country' => $this->country,
            'debitor_account_number' => $this->debitorAccountNumber,
        ];
    }

    /**
     * Convenience-Methode für neue Clients (id=null).
     */
    public static function new(
        string  $name,
        ?string $firstName = null,
        ?string $lastName = null,
        ?string $salutation = null,
        ?string $clientNumber = null,
        ?string $email = null,
        ?string $phone = null,
        ?string $street = null,
        ?string $zip = null,
        ?string $city = null,
        ?string $country = null,
        ?int    $debitorAccountNumber = null
    ): self
    {
        return new self(
            id: null,
            name: $name,
            firstName: $firstName,
            lastName: $lastName,
            salutation: $salutation,
            clientNumber: $clientNumber,
            email: $email,
            phone: $phone,
            street: $street,
            zip: $zip,
            city: $city,
            country: $country,
            debitorAccountNumber: $debitorAccountNumber
        );
    }
}