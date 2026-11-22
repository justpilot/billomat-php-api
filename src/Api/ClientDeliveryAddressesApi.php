<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Generator;
use Justpilot\Billomat\Exception\AuthenticationException;
use Justpilot\Billomat\Exception\HttpException;
use Justpilot\Billomat\Exception\NotFoundException;
use Justpilot\Billomat\Exception\ValidationException;
use Justpilot\Billomat\Model\ClientDeliveryAddress;
use Justpilot\Billomat\Pagination\Page;
use NoDiscard;

/**
 * API-Wrapper für die Lieferadressen der Kunden, erreichbar als `$billomat->clients->deliveryAddresses`.
 *
 * Doku: https://www.billomat.com/api/lieferadressen/
 *
 * Endpoints:
 *  - GET    /client-delivery-addresses?client_id={id}
 *  - GET    /client-delivery-addresses/{id}
 *  - POST   /client-delivery-addresses
 *  - PUT    /client-delivery-addresses/{id}
 *  - DELETE /client-delivery-addresses/{id}
 *
 * Eine Lieferadresse nutzt man über `clientDeliveryAddressId` in den Options von Rechnungen
 * und Abo-Rechnungen. Ein `customfield` unterstützt die Ressource nicht.
 *
 * @experimental Billomat stellt die Lieferadressen noch fertig; laut Doku gelten die Endpunkte erst ab dem
 *               01.01.2027 verbindlich. Bis dahin kann sich die Klasse auch in einem Minor-Release ändern
 *               (siehe docs/concepts/backward-compatibility.md).
 */
final class ClientDeliveryAddressesApi extends AbstractApi
{
    /**
     * Listet die Lieferadressen eines Kunden.
     *
     * @param int                                                             $parentId ID des Kunden (`client_id`)
     * @param SubResourceFilter|array<string, scalar|array<scalar|null>|null> $filters  Sortierung, Seite, …
     *
     * @return list<ClientDeliveryAddress>
     */
    #[NoDiscard]
    public function list(int $parentId, SubResourceFilter|array $filters = []): array
    {
        return $this->listResource('/client-delivery-addresses', 'client-delivery-addresses', 'client-delivery-address', ClientDeliveryAddress::fromArray(...), $this->clientFilter($parentId, $filters));
    }

    /**
     * Wie {@see list()}, liefert aber zusätzlich die Pagination-Metadaten.
     *
     * @param int                                                             $parentId ID des Kunden (`client_id`)
     * @param SubResourceFilter|array<string, scalar|array<scalar|null>|null> $filters  Sortierung, Seite, …
     *
     * @return Page<ClientDeliveryAddress>
     */
    #[NoDiscard]
    public function listPage(int $parentId, SubResourceFilter|array $filters = []): Page
    {
        return $this->listResourcePage('/client-delivery-addresses', 'client-delivery-addresses', 'client-delivery-address', ClientDeliveryAddress::fromArray(...), $this->clientFilter($parentId, $filters));
    }

    /**
     * Iteriert lazy über alle Lieferadressen eines Kunden.
     *
     * @param int                                                             $parentId ID des Kunden (`client_id`)
     * @param SubResourceFilter|array<string, scalar|array<scalar|null>|null> $filters  Sortierung, Seite, …
     *
     * @return Generator<int, ClientDeliveryAddress>
     */
    #[NoDiscard]
    public function iterateAll(int $parentId, SubResourceFilter|array $filters = [], int $pageSize = 100): Generator
    {
        yield from $this->iterateResource('/client-delivery-addresses', 'client-delivery-addresses', 'client-delivery-address', ClientDeliveryAddress::fromArray(...), $this->clientFilter($parentId, $filters), $pageSize);
    }

    #[NoDiscard]
    public function get(int $id): ?ClientDeliveryAddress
    {
        return $this->getResource("/client-delivery-addresses/{$id}", 'client-delivery-address', ClientDeliveryAddress::fromArray(...), 'fetching client delivery address');
    }

    /**
     * Wie {@see get()}, wirft aber eine {@see NotFoundException} statt null zurückzugeben.
     *
     * @throws NotFoundException wenn es den Datensatz nicht gibt (404)
     */
    #[NoDiscard]
    public function getOrFail(int $id): ClientDeliveryAddress
    {
        return $this->get($id) ?? throw $this->notFound("/client-delivery-addresses/{$id}");
    }

    /**
     * Legt eine Lieferadresse bei einem Kunden an.
     *
     * @param int $parentId ID des Kunden (`client_id`)
     *
     * @throws ValidationException     z. B. bei einem ungültigen Ländercode („no valid country_code given“)
     * @throws AuthenticationException
     * @throws HttpException
     */
    public function create(int $parentId, ClientDeliveryAddressCreateOptions $options): ClientDeliveryAddress
    {
        $payload = ['client-delivery-address' => [...$options->toArray(), 'client_id' => $parentId]];

        $data = $this->postJson('/client-delivery-addresses', $payload);

        return ClientDeliveryAddress::fromArray($this->unwrapEnvelope($data, 'client-delivery-address', 'creating client delivery address'));
    }

    public function update(int $id, ClientDeliveryAddressUpdateOptions $options): ClientDeliveryAddress
    {
        $payload = ['client-delivery-address' => $options->toArray()];

        $data = $this->putJson("/client-delivery-addresses/{$id}", $payload);

        return ClientDeliveryAddress::fromArray($this->unwrapEnvelope($data, 'client-delivery-address', 'updating client delivery address'));
    }

    public function delete(int $id): void
    {
        $this->deleteVoid("/client-delivery-addresses/{$id}");
    }

    /**
     * `client_id` ist die Grundlage der Liste und hat Vorrang vor einem gleichnamigen Filter.
     *
     * @param SubResourceFilter|array<string, scalar|array<scalar|null>|null> $filters
     *
     * @return array<string, scalar|array<scalar|null>|null>
     */
    private function clientFilter(int $parentId, SubResourceFilter|array $filters): array
    {
        return ['client_id' => $parentId] + self::filterQuery($filters);
    }
}
