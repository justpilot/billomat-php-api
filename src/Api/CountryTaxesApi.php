<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Generator;
use Justpilot\Billomat\Model\CountryTax;
use Justpilot\Billomat\Pagination\Page;

/**
 * Read-only API-Wrapper für die Liste steuerfreier Länder
 * (`/country-taxes`).
 *
 * Quelle: https://www.billomat.com/api/einstellungen/steuerfreie-laender/
 *
 * Billomat unterstützt zusätzlich POST/PUT/DELETE auf `/country-taxes`. Das
 * SDK exponiert diese Verben aktuell nicht.
 *
 * Filter:
 *  - `country` — ISO 3166 Alpha-2 Ländercode (exact).
 */
final class CountryTaxesApi extends AbstractApi
{
    /**
     * @param array<string, scalar|array|null> $filters
     *
     * @return list<CountryTax>
     */
    public function list(array $filters = []): array
    {
        return $this->listResource('/country-taxes', 'country-taxes', 'country-tax', CountryTax::fromArray(...), $filters);
    }

    /**
     * @param array<string, scalar|array|null> $filters
     *
     * @return Page<CountryTax>
     */
    public function listPage(array $filters = []): Page
    {
        return $this->listResourcePage('/country-taxes', 'country-taxes', 'country-tax', CountryTax::fromArray(...), $filters);
    }

    /**
     * @param array<string, scalar|array|null> $filters
     *
     * @return Generator<int, CountryTax>
     */
    public function iterateAll(array $filters = [], int $pageSize = 100): Generator
    {
        yield from $this->iterateResource('/country-taxes', 'country-taxes', 'country-tax', CountryTax::fromArray(...), $filters, $pageSize);
    }

    public function get(int $id): ?CountryTax
    {
        $data = $this->getJsonOrNull("/country-taxes/{$id}");

        if (null === $data) {
            return null;
        }

        $row = $data['country-tax'] ?? null;
        if (!\is_array($row)) {
            return null;
        }

        return CountryTax::fromArray($row);
    }
}
