<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Exception\AuthenticationException;
use Justpilot\Billomat\Exception\HttpException;
use Justpilot\Billomat\Exception\NotFoundException;
use Justpilot\Billomat\Exception\ValidationException;
use Justpilot\Billomat\Model\Contact;
use NoDiscard;

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
     * @param array<string, scalar|array<mixed>|null> $query
     *
     * @return list<Contact>
     */
    #[NoDiscard]
    public function list(int $clientId, array $query = []): array
    {
        $params = array_merge(['client_id' => $clientId], $query);

        return $this->listResource('/contacts', 'contacts', 'contact', Contact::fromArray(...), $params);
    }

    #[NoDiscard]
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
     * Wie {@see get()}, wirft aber eine {@see NotFoundException} statt null zurückzugeben.
     *
     * @throws NotFoundException wenn es den Datensatz nicht gibt (404)
     */
    #[NoDiscard]
    public function getOrFail(int $id): Contact
    {
        return $this->get($id) ?? throw $this->notFound("/contacts/{$id}");
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

    public function delete(int $id): void
    {
        $this->deleteVoid("/contacts/{$id}");
    }
}
