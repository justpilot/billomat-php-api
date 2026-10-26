<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Generator;
use Justpilot\Billomat\Exception\AuthenticationException;
use Justpilot\Billomat\Exception\HttpException;
use Justpilot\Billomat\Exception\NotFoundException;
use Justpilot\Billomat\Exception\ValidationException;
use Justpilot\Billomat\Model\Contact;
use Justpilot\Billomat\Pagination\Page;
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
 *  - GET    /contacts/{id}/avatar
 *
 * `client_id` ist beim Listen Pflicht.
 */
final class ContactsApi extends AbstractApi
{
    /**
     * Listet die Ansprechpartner eines Kunden.
     *
     * @param array<string, scalar|array<scalar|null>|null> $filters
     *
     * @return list<Contact>
     */
    #[NoDiscard]
    public function list(int $clientId, array $filters = []): array
    {
        return $this->listResource('/contacts', 'contacts', 'contact', Contact::fromArray(...), $this->clientFilter($clientId, $filters));
    }

    /**
     * Wie {@see list()}, liefert aber zusätzlich die Pagination-Metadaten.
     *
     * @param array<string, scalar|array<scalar|null>|null> $filters
     *
     * @return Page<Contact>
     */
    #[NoDiscard]
    public function listPage(int $clientId, array $filters = []): Page
    {
        return $this->listResourcePage('/contacts', 'contacts', 'contact', Contact::fromArray(...), $this->clientFilter($clientId, $filters));
    }

    /**
     * Iteriert lazy über alle Ansprechpartner eines Kunden.
     *
     * @param array<string, scalar|array<scalar|null>|null> $filters
     *
     * @return Generator<int, Contact>
     */
    #[NoDiscard]
    public function iterateAll(int $clientId, array $filters = [], int $pageSize = 100): Generator
    {
        yield from $this->iterateResource('/contacts', 'contacts', 'contact', Contact::fromArray(...), $this->clientFilter($clientId, $filters), $pageSize);
    }

    #[NoDiscard]
    public function get(int $id): ?Contact
    {
        return $this->getResource("/contacts/{$id}", 'contact', Contact::fromArray(...), 'fetching contact');
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

    /**
     * Lädt das Avatar (binäres Bild) eines Ansprechpartners.
     *
     * Entspricht GET /contacts/{id}/avatar?size={size}. Billomat liefert kein JSON, sondern direkt
     * das Bild (i. d. R. PNG); Rückgabe ist der rohe Body.
     *
     * @param int|null $size optionale Kantenlänge in Pixel
     */
    #[NoDiscard]
    public function avatar(int $id, ?int $size = null): string
    {
        $query = null !== $size ? ['size' => $size] : [];

        return $this->getRaw("/contacts/{$id}/avatar", $query);
    }

    /**
     * `client_id` ist beim Listen Pflicht und hat Vorrang vor einem gleichnamigen Filter.
     *
     * @param array<string, scalar|array<scalar|null>|null> $filters
     *
     * @return array<string, scalar|array<scalar|null>|null>
     */
    private function clientFilter(int $clientId, array $filters): array
    {
        return ['client_id' => $clientId] + $filters;
    }
}
