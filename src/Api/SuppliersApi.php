<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Generator;
use Justpilot\Billomat\Exception\AuthenticationException;
use Justpilot\Billomat\Exception\HttpException;
use Justpilot\Billomat\Exception\NotFoundException;
use Justpilot\Billomat\Exception\UnexpectedResponseException;
use Justpilot\Billomat\Exception\ValidationException;
use Justpilot\Billomat\Model\Supplier;
use Justpilot\Billomat\Model\SupplierProperty;
use Justpilot\Billomat\Model\SupplierPropertyValue;
use Justpilot\Billomat\Model\SupplierTag;
use Justpilot\Billomat\Model\SupplierTagCloudEntry;
use Justpilot\Billomat\Pagination\Page;
use NoDiscard;

/**
 * API-Wrapper für die Billomat-Suppliers-Ressource (Lieferanten).
 *
 * Doku: https://www.billomat.com/en/api/suppliers/
 *
 * Endpoints:
 *  - GET    /suppliers
 *  - GET    /suppliers/{id}
 *  - POST   /suppliers
 *  - PUT    /suppliers/{id}
 *  - DELETE /suppliers/{id}
 */
final class SuppliersApi extends AbstractApi
{
    /**
     * Schlagworte, siehe {@see TagsApi}.
     *
     * @var TagsApi<SupplierTag, SupplierTagCloudEntry, SupplierTagCreateOptions>
     */
    public private(set) TagsApi $tags {
        get => $this->tags ??= new TagsApi($this->http, 'supplier', SupplierTag::fromArray(...), SupplierTagCloudEntry::fromArray(...), SupplierTagCreateOptions::class);
    }

    /**
     * Attribut-Definitionen, siehe {@see PropertiesApi}.
     *
     * @var PropertiesApi<SupplierProperty>
     */
    public private(set) PropertiesApi $properties {
        get => $this->properties ??= new PropertiesApi($this->http, 'supplier', SupplierProperty::fromArray(...));
    }

    /**
     * Attributwerte, siehe {@see PropertyValuesApi}.
     *
     * @var PropertyValuesApi<SupplierPropertyValue, SupplierPropertyValueCreateOptions, SupplierPropertyValueFilter>
     */
    public private(set) PropertyValuesApi $propertyValues {
        get => $this->propertyValues ??= new PropertyValuesApi($this->http, 'supplier', SupplierPropertyValue::fromArray(...), SupplierPropertyValueCreateOptions::class, SupplierPropertyValueFilter::class);
    }

    /**
     * @param SupplierFilter|array<string, scalar|array<scalar|null>|null> $filters
     *
     * @return list<Supplier>
     */
    #[NoDiscard]
    public function list(SupplierFilter|array $filters = []): array
    {
        return $this->listResource('/suppliers', 'suppliers', 'supplier', Supplier::fromArray(...), $filters);
    }

    /**
     * Liefert eine einzelne Seite samt Pagination-Metadaten.
     *
     * Identisch zu {@see list()}, gibt aber zusätzlich `@page`/`@per_page`/
     * `@total` aus dem Response-Envelope als {@see PageInfo} zurück. Nützlich
     * für UI mit klassischer "Seite 1/12, 234 Treffer"-Anzeige.
     *
     * @param SupplierFilter|array<string, scalar|array<scalar|null>|null> $filters
     *
     * @return Page<Supplier>
     */
    #[NoDiscard]
    public function listPage(SupplierFilter|array $filters = []): Page
    {
        return $this->listResourcePage('/suppliers', 'suppliers', 'supplier', Supplier::fromArray(...), $filters);
    }

    /**
     * Iteriert lazy durch alle Lieferanten und yieldet sie einzeln.
     *
     * Holt seitenweise pro {@code $pageSize}-Items und stoppt automatisch,
     * sobald die letzte Seite erreicht ist (analog `auto_paging_iter()` im
     * Stripe-SDK). Filter werden bei jeder Page-Anfrage mitgesendet; `page`
     * und `per_page` darin werden überschrieben.
     *
     * @param SupplierFilter|array<string, scalar|array<scalar|null>|null> $filters
     *
     * @return Generator<int, Supplier>
     */
    #[NoDiscard]
    public function iterateAll(SupplierFilter|array $filters = [], int $pageSize = 100): Generator
    {
        yield from $this->iterateResource('/suppliers', 'suppliers', 'supplier', Supplier::fromArray(...), $filters, $pageSize);
    }

    #[NoDiscard]
    public function get(int $id): ?Supplier
    {
        return $this->getResource("/suppliers/{$id}", 'supplier', Supplier::fromArray(...), 'fetching supplier');
    }

    /**
     * Wie {@see get()}, wirft aber eine {@see NotFoundException} statt null zurückzugeben.
     *
     * @throws NotFoundException wenn es den Datensatz nicht gibt (404)
     */
    #[NoDiscard]
    public function getOrFail(int $id): Supplier
    {
        return $this->get($id) ?? throw $this->notFound("/suppliers/{$id}");
    }

    /**
     * @throws ValidationException
     * @throws AuthenticationException
     * @throws HttpException
     */
    public function create(SupplierCreateOptions $options): Supplier
    {
        $payload = ['supplier' => $options->toArray()];

        $data = $this->postJson('/suppliers', $payload);

        $created = $data['supplier'] ?? null;

        if (!self::isRecord($created)) {
            throw new UnexpectedResponseException('Unexpected response from Billomat when creating supplier.');
        }

        return Supplier::fromArray($created);
    }

    public function update(int $id, SupplierUpdateOptions $options): Supplier
    {
        $payload = ['supplier' => $options->toArray()];

        $data = $this->putJson("/suppliers/{$id}", $payload);

        $row = $data['supplier'] ?? null;
        if (!self::isRecord($row)) {
            throw new UnexpectedResponseException('Unexpected response from Billomat when updating supplier.');
        }

        return Supplier::fromArray($row);
    }

    public function delete(int $id): void
    {
        $this->deleteVoid("/suppliers/{$id}");
    }
}
