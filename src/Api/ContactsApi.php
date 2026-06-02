<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Exception\AuthenticationException;
use Justpilot\Billomat\Exception\HttpException;
use Justpilot\Billomat\Exception\ValidationException;
use Justpilot\Billomat\Model\Contact;

/**
 * API-Wrapper für Kunden-Ansprechpartner (Contacts).
 *
 * Doku: https://www.billomat.com/en/api/clients/contacts/
 *
 * Endpoints:
 *  - GET    /contacts?client_id={id}
 *  - GET    /contacts/{id}
 *  - POST   /contacts
 *  - PUT    /contacts/{id}
 *  - DELETE /contacts/{id}
 *
 * `client_id` ist beim Listen Pflicht.
 */
final class ContactsApi extends AbstractApi
{
    /**
     * @param array<string, scalar|array|null> $query
     *
     * @return list<Contact>
     */
    public function listByClient(int $clientId, array $query = []): array
    {
        $params = array_merge(['client_id' => $clientId], $query);

        return $this->listResource('/contacts', 'contacts', 'contact', Contact::fromArray(...), $params);
    }

    public function get(int $id): ?Contact
    {
        $data = $this->getJsonOrNull("/contacts/{$id}");

        if (null === $data) {
            return null;
        }

        $row = $data['contact'] ?? null;

        if (!\is_array($row)) {
            return null;
        }

        return Contact::fromArray($row);
    }

    /**
     * @throws ValidationException
     * @throws AuthenticationException
     * @throws HttpException
     */
    public function create(ContactCreateOptions $options): Contact
    {
        $payload = ['contact' => $options->toArray()];

        $data = $this->postJson('/contacts', $payload);

        return Contact::fromArray($this->unwrapEnvelope($data, 'contact', 'creating contact'));
    }

    public function update(int $id, ContactUpdateOptions $options): Contact
    {
        $payload = ['contact' => $options->toArray()];

        $data = $this->putJson("/contacts/{$id}", $payload);

        return Contact::fromArray($this->unwrapEnvelope($data, 'contact', 'updating contact'));
    }

    public function delete(int $id): bool
    {
        $this->deleteVoid("/contacts/{$id}");

        return true;
    }
}
