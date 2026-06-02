<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Exception\AuthenticationException;
use Justpilot\Billomat\Exception\HttpException;
use Justpilot\Billomat\Exception\ValidationException;
use Justpilot\Billomat\Model\Contact;
use RuntimeException;

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

        $data = $this->getJson('/contacts', $params);

        $node = $data['contacts']['contact'] ?? [];

        if (null === $node || [] === $node) {
            return [];
        }

        if (\is_array($node) && array_is_list($node)) {
            $rows = $node;
        } elseif (\is_array($node)) {
            $rows = [$node];
        } else {
            $rows = [];
        }

        /** @var list<Contact> $models */
        $models = array_map(
            Contact::fromArray(...),
            $rows,
        );

        return $models;
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

        $created = $data['contact'] ?? null;

        if (!\is_array($created)) {
            throw new RuntimeException('Unexpected response from Billomat when creating contact.');
        }

        return Contact::fromArray($created);
    }

    public function update(int $id, ContactUpdateOptions $options): Contact
    {
        $payload = ['contact' => $options->toArray()];

        $data = $this->putJson("/contacts/{$id}", $payload);

        $row = $data['contact'] ?? null;
        if (!\is_array($row)) {
            throw new RuntimeException('Unexpected response from Billomat when updating contact.');
        }

        return Contact::fromArray($row);
    }

    public function delete(int $id): bool
    {
        $this->deleteVoid("/contacts/{$id}");

        return true;
    }
}
