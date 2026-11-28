<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Generator;
use Justpilot\Billomat\Http\BillomatHttpClientInterface;
use Justpilot\Billomat\Model\ClientDeliveryAddress;
use Justpilot\Billomat\Pagination\Page;
use NoDiscard;

/**
 * Lieferadressen eines Kunden.
 *
 * Erreichbar über `$billomat->clients->deliveryAddresses`. Rechnungen und Abo-Rechnungen
 * verweisen über `clientDeliveryAddressId` auf eine Lieferadresse.
 *
 * Endpoints:
 *  - GET    /client-delivery-addresses[?client_id={id}]
 *  - GET    /client-delivery-addresses/{id}
 *  - POST   /client-delivery-addresses
 *  - PUT    /client-delivery-addresses/{id}
 *  - DELETE /client-delivery-addresses/{id}
 *
 * Doku: https://www.billomat.com/api/lieferadressen/
 *
 * @extends AbstractSubResourceApi<ClientDeliveryAddress>
 */
final class ClientDeliveryAddressesApi extends AbstractSubResourceApi
{
    protected const string PLURAL = 'delivery-addresses';
    protected const string SINGULAR = 'delivery-address';

    /**
     * @internal Instanzen kommen über `$billomat->clients->deliveryAddresses`
     */
    public function __construct(BillomatHttpClientInterface $http)
    {
        parent::__construct($http, 'client', ClientDeliveryAddress::fromArray(...));
    }

    /**
     * Listet die Lieferadressen eines Kunden oder, ohne Kunden-ID, aller Kunden.
     *
     * @param int|null                                                        $parentId ID des Kunden (`client_id`); null = Lieferadressen aller Kunden
     * @param SubResourceFilter|array<string, scalar|array<scalar|null>|null> $filters  Sortierung, Seite, …
     *
     * @return list<ClientDeliveryAddress>
     */
    #[NoDiscard]
    public function list(?int $parentId = null, SubResourceFilter|array $filters = []): array
    {
        return $this->listFor($parentId, $filters);
    }

    /**
     * Wie {@see list()}, liefert aber zusätzlich die Pagination-Metadaten.
     *
     * @param int|null                                                        $parentId ID des Kunden (`client_id`); null = Lieferadressen aller Kunden
     * @param SubResourceFilter|array<string, scalar|array<scalar|null>|null> $filters  Sortierung, Seite, …
     *
     * @return Page<ClientDeliveryAddress>
     */
    #[NoDiscard]
    public function listPage(?int $parentId = null, SubResourceFilter|array $filters = []): Page
    {
        return $this->pageFor($parentId, $filters);
    }

    /**
     * Iteriert lazy über alle Lieferadressen eines Kunden oder aller Kunden.
     *
     * @param int|null                                                        $parentId ID des Kunden (`client_id`); null = Lieferadressen aller Kunden
     * @param SubResourceFilter|array<string, scalar|array<scalar|null>|null> $filters  `page`/`per_page` werden überschrieben
     *
     * @return Generator<int, ClientDeliveryAddress>
     */
    #[NoDiscard]
    public function iterateAll(?int $parentId = null, SubResourceFilter|array $filters = [], int $pageSize = 100): Generator
    {
        yield from $this->iterateFor($parentId, $filters, $pageSize);
    }

    /**
     * Legt eine Lieferadresse bei einem Kunden an.
     *
     * @param int $parentId ID des Kunden (`client_id`)
     */
    public function create(int $parentId, ClientDeliveryAddressCreateOptions $options): ClientDeliveryAddress
    {
        return $this->createFor($parentId, $options);
    }

    /**
     * Ändert eine Lieferadresse; der Kunde lässt sich nicht ändern.
     */
    public function update(int $id, ClientDeliveryAddressUpdateOptions $options): ClientDeliveryAddress
    {
        return $this->updateFrom($id, $options->toArray());
    }

    public function delete(int $id): void
    {
        $this->deleteById($id);
    }
}
