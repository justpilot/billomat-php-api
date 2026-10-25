<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Generator;
use Justpilot\Billomat\Exception\NotFoundException;
use Justpilot\Billomat\Model\CountryTax;
use Justpilot\Billomat\Pagination\Page;
use NoDiscard;

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
     * @param array<string, scalar|array<scalar|null>|null> $filters
     *
     * @return list<CountryTax>
     */
    #[NoDiscard]
    public function list(array $filters = []): array
    {
        return $this->listResource('/country-taxes', 'country-taxes', 'country-tax', CountryTax::fromArray(...), $filters);
    }

    /**
     * @param array<string, scalar|array<scalar|null>|null> $filters
     *
     * @return Page<CountryTax>
     */
    #[NoDiscard]
    public function listPage(array $filters = []): Page
    {
        return $this->listResourcePage('/country-taxes', 'country-taxes', 'country-tax', CountryTax::fromArray(...), $filters);
    }

    /**
     * @param array<string, scalar|array<scalar|null>|null> $filters
     *
     * @return Generator<int, CountryTax>
     */
    #[NoDiscard]
    public function iterateAll(array $filters = [], int $pageSize = 100): Generator
    {
        yield from $this->iterateResource('/country-taxes', 'country-taxes', 'country-tax', CountryTax::fromArray(...), $filters, $pageSize);
    }

    #[NoDiscard]
    public function get(int $id): ?CountryTax
    {
        $data = $this->getJsonOrNull("/country-taxes/{$id}");

        if (null === $data) {
            return null;
        }

        $row = $data['country-tax'] ?? null;
        if (!self::isRecord($row)) {
            return null;
        }

        return CountryTax::fromArray($row);
    }

    /**
     * Wie {@see get()}, wirft aber eine {@see NotFoundException} statt null zurückzugeben.
     *
     * @throws NotFoundException wenn es den Datensatz nicht gibt (404)
     */
    #[NoDiscard]
    public function getOrFail(int $id): CountryTax
    {
        return $this->get($id) ?? throw $this->notFound("/country-taxes/{$id}");
    }
}
