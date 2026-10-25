<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Generator;
use Justpilot\Billomat\Exception\AuthenticationException;
use Justpilot\Billomat\Exception\HttpException;
use Justpilot\Billomat\Exception\NotFoundException;
use Justpilot\Billomat\Exception\UnexpectedResponseException;
use Justpilot\Billomat\Exception\ValidationException;
use Justpilot\Billomat\Model\Client;
use Justpilot\Billomat\Model\ClientProperty;
use Justpilot\Billomat\Model\ClientPropertyValue;
use Justpilot\Billomat\Model\ClientTag;
use Justpilot\Billomat\Model\ClientTagCloudEntry;
use Justpilot\Billomat\Pagination\Page;
use NoDiscard;

/**
 * API-Wrapper für die Billomat-Clients-Ressource.
 *
 * Kapselt Zugriffe auf:
 *  - GET    /clients
 *  - GET    /clients/myself
 *  - GET    /clients/{id}
 *  - POST   /clients
 *  - PUT    /clients/{id}
 *  - DELETE /clients/{id}
 *  - GET    /clients/{id}/avatar
 */
final class ClientsApi extends AbstractApi
{
    /**
     * Schlagworte, siehe {@see TagsApi}.
     *
     * @var TagsApi<ClientTag, ClientTagCloudEntry, ClientTagCreateOptions>
     */
    public private(set) TagsApi $tags {
        get => $this->tags ??= new TagsApi($this->http, 'client', ClientTag::fromArray(...), ClientTagCloudEntry::fromArray(...), ClientTagCreateOptions::class);
    }

    /**
     * Attribut-Definitionen, siehe {@see PropertiesApi}.
     *
     * @var PropertiesApi<ClientProperty>
     */
    public private(set) PropertiesApi $properties {
        get => $this->properties ??= new PropertiesApi($this->http, 'client', ClientProperty::fromArray(...));
    }

    /**
     * Attributwerte, siehe {@see PropertyValuesApi}.
     *
     * @var PropertyValuesApi<ClientPropertyValue, ClientPropertyValueCreateOptions>
     */
    public private(set) PropertyValuesApi $propertyValues {
        get => $this->propertyValues ??= new PropertyValuesApi($this->http, 'client', ClientPropertyValue::fromArray(...), ClientPropertyValueCreateOptions::class);
    }

    #[NoDiscard]
    public function getMyself(): Client
    {
        $data = $this->getJson('/clients/myself');

        return Client::fromArray($this->unwrapEnvelope($data, 'client', 'fetching own account via /clients/myself'));
    }

    /**
     * Listet Clients mit optionalen Filtern.
     *
     * Entspricht GET /clients
     *
     * @param ClientFilter|array<string, scalar|array<scalar|null>|null> $filters
     *
     * @return list<Client>
     */
    #[NoDiscard]
    public function list(ClientFilter|array $filters = []): array
    {
        return $this->listResource('/clients', 'clients', 'client', Client::fromArray(...), $filters);
    }

    /**
     * Liefert eine einzelne Seite samt Pagination-Metadaten.
     *
     * Identisch zu {@see list()}, gibt aber zusätzlich `@page`/`@per_page`/
     * `@total` aus dem Response-Envelope als {@see PageInfo} zurück. Nützlich
     * für UI mit klassischer "Seite 1/12, 234 Treffer"-Anzeige.
     *
     * @param ClientFilter|array<string, scalar|array<scalar|null>|null> $filters
     *
     * @return Page<Client>
     */
    #[NoDiscard]
    public function listPage(ClientFilter|array $filters = []): Page
    {
        return $this->listResourcePage('/clients', 'clients', 'client', Client::fromArray(...), $filters);
    }

    /**
     * Iteriert lazy durch alle Clients und yieldet sie einzeln.
     *
     * Holt seitenweise pro {@code $pageSize}-Items und stoppt automatisch,
     * sobald die letzte Seite erreicht ist (analog `auto_paging_iter()` im
     * Stripe-SDK). Filter werden bei jeder Page-Anfrage mitgesendet; `page`
     * und `per_page` darin werden überschrieben.
     *
     * @param ClientFilter|array<string, scalar|array<scalar|null>|null> $filters
     *
     * @return Generator<int, Client>
     */
    #[NoDiscard]
    public function iterateAll(ClientFilter|array $filters = [], int $pageSize = 100): Generator
    {
        yield from $this->iterateResource('/clients', 'clients', 'client', Client::fromArray(...), $filters, $pageSize);
    }

    /**
     * Holt einen einzelnen Client, oder null wenn nicht gefunden.
     *
     * Entspricht GET /clients/{id}
     */
    #[NoDiscard]
    public function get(int $id): ?Client
    {
        $data = $this->getJsonOrNull("/clients/{$id}");

        if (null === $data) {
            return null;
        }

        $clientData = $data['client'] ?? null;

        if (!self::isRecord($clientData)) {
            return null;
        }

        return Client::fromArray($clientData);
    }

    /**
     * Wie {@see get()}, wirft aber eine {@see NotFoundException} statt null zurückzugeben.
     *
     * @throws NotFoundException wenn es den Datensatz nicht gibt (404)
     */
    #[NoDiscard]
    public function getOrFail(int $id): Client
    {
        return $this->get($id) ?? throw $this->notFound("/clients/{$id}");
    }

    /**
     * Legt einen neuen Client an.
     *
     * Entspricht POST /clients
     *
     * @throws UnexpectedResponseException Wenn die Response-Struktur unerwartet ist
     */
    public function create(ClientCreateOptions $options): Client
    {
        $data = $this->postJson('/clients', ['client' => $options->toArray()]);

        return Client::fromArray($this->unwrapEnvelope($data, 'client', 'creating client'));
    }

    /**
     * Aktualisiert einen bestehenden Kunden in Billomat.
     *
     * Entspricht:
     * PUT /api/clients/{id}
     *
     * ⚠️ Einschränkungen laut Billomat:
     * - Ein Kunde kann nur aktualisiert werden, wenn er nicht archiviert ist.
     * - Es werden ausschließlich die im Payload gesetzten Felder geändert
     *   (Partial Update).
     * - Nicht unterstützte oder leere Felder werden ignoriert.
     *
     * Der Request wird mit einem "client"-Wrapper gesendet, wie von der
     * Billomat-API erwartet:
     *
     * {
     *   "client": { ... }
     * }
     *
     * @param int                 $id
     *                                     Die interne Billomat-ID des Kunden
     * @param ClientUpdateOptions $options
     *                                     Die zu ändernden Kundendaten. Nur gesetzte Felder werden übertragen.
     *
     * @return Client
     *                Das aktualisierte Client-Objekt, wie von der Billomat-API zurückgegeben
     *
     * @throws ValidationException
     *                                     Bei ungültigen oder nicht erlaubten Änderungen (HTTP 400/422)
     * @throws AuthenticationException
     *                                     Bei ungültigen API-Zugangsdaten (HTTP 401/403)
     * @throws HttpException
     *                                     Bei allen sonstigen HTTP-Fehlern
     * @throws UnexpectedResponseException
     *                                     Wenn die API ein unerwartetes oder unvollständiges Response-Format liefert
     */
    public function update(int $id, ClientUpdateOptions $options): Client
    {
        $data = $this->putJson("/clients/{$id}", ['client' => $options->toArray()]);

        return Client::fromArray($this->unwrapEnvelope($data, 'client', 'updating client'));
    }

    /**
     * Löscht einen Kunden.
     *
     * Entspricht DELETE /clients/{id}.
     *
     * ⚠️ Hinweis aus der Billomat-Doku: Kunden, an denen bereits Dokumente
     * (Rechnungen, Angebote, …) hängen, können nicht hart gelöscht, sondern
     * nur archiviert werden. In diesem Fall wirft Billomat 400/422 und die
     * SDK propagiert eine {@see ValidationException}.
     */
    public function delete(int $id): void
    {
        $this->deleteVoid("/clients/{$id}");
    }

    /**
     * Lädt das Avatar (binäres Bild) eines Kunden.
     *
     * Entspricht GET /clients/{id}/avatar?size={size}.
     *
     * Billomat liefert hier kein JSON, sondern direkt das Bild
     * (i. d. R. PNG). Rückgabe ist der rohe Body-String.
     *
     * @param int|null $size optionale Kantenlänge in Pixel
     */
    #[NoDiscard]
    public function avatar(int $id, ?int $size = null): string
    {
        $query = null !== $size ? ['size' => $size] : [];

        return $this->getRaw("/clients/{$id}/avatar", $query);
    }
}
