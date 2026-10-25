<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Generator;
use Justpilot\Billomat\Exception\AuthenticationException;
use Justpilot\Billomat\Exception\HttpException;
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
     * @var PropertyValuesApi<SupplierPropertyValue, SupplierPropertyValueCreateOptions>
     */
    public private(set) PropertyValuesApi $propertyValues {
        get => $this->propertyValues ??= new PropertyValuesApi($this->http, 'supplier', SupplierPropertyValue::fromArray(...), SupplierPropertyValueCreateOptions::class);
    }

    /**
     * @param array<string, scalar|array|null> $filters
     *
     * @return list<Supplier>
     */
    #[NoDiscard]
    public function list(array $filters = []): array
    {
        $data = $this->getJson('/suppliers', $filters);

        $node = $data['suppliers']['supplier'] ?? [];

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

        /** @var list<Supplier> $models */
        $models = array_map(
            Supplier::fromArray(...),
            $rows,
        );

        return $models;
    }

    /**
     * Liefert eine einzelne Seite samt Pagination-Metadaten.
     *
     * Identisch zu {@see list()}, gibt aber zusätzlich `@page`/`@per_page`/
     * `@total` aus dem Response-Envelope als {@see PageInfo} zurück. Nützlich
     * für UI mit klassischer "Seite 1/12, 234 Treffer"-Anzeige.
     *
     * @param array<string, scalar|array|null> $filters
     *
     * @return Page<Supplier>
     */
    #[NoDiscard]
    public function listPage(array $filters = []): Page
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
     * @param array<string, scalar|array|null> $filters
     *
     * @return Generator<int, Supplier>
     */
    #[NoDiscard]
    public function iterateAll(array $filters = [], int $pageSize = 100): Generator
    {
        yield from $this->iterateResource('/suppliers', 'suppliers', 'supplier', Supplier::fromArray(...), $filters, $pageSize);
    }

    #[NoDiscard]
    public function get(int $id): ?Supplier
    {
        $data = $this->getJsonOrNull("/suppliers/{$id}");

        if (null === $data) {
            return null;
        }

        $row = $data['supplier'] ?? null;

        if (!\is_array($row)) {
            return null;
        }

        return Supplier::fromArray($row);
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

        if (!\is_array($created)) {
            throw new UnexpectedResponseException('Unexpected response from Billomat when creating supplier.');
        }

        return Supplier::fromArray($created);
    }

    public function update(int $id, SupplierUpdateOptions $options): Supplier
    {
        $payload = ['supplier' => $options->toArray()];

        $data = $this->putJson("/suppliers/{$id}", $payload);

        $row = $data['supplier'] ?? null;
        if (!\is_array($row)) {
            throw new UnexpectedResponseException('Unexpected response from Billomat when updating supplier.');
        }

        return Supplier::fromArray($row);
    }

    public function delete(int $id): void
    {
        $this->deleteVoid("/suppliers/{$id}");
    }
}
