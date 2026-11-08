<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Generator;
use Justpilot\Billomat\Exception\NotFoundException;
use Justpilot\Billomat\Model\CountryTax;
use Justpilot\Billomat\Pagination\Page;
use NoDiscard;

/**
 * API-Wrapper für die Liste steuerfreier Länder
 * (`/country-taxes`).
 *
 * Quelle: https://www.billomat.com/api/einstellungen/steuerfreie-laender/
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
        return $this->getResource("/country-taxes/{$id}", 'country-tax', CountryTax::fromArray(...), 'fetching country tax');
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

    /**
     * Trägt ein steuerfreies Land ein.
     */
    public function create(CountryTaxCreateOptions $options): CountryTax
    {
        $data = $this->postJson('/country-taxes', ['country-tax' => $options->toArray()]);

        return CountryTax::fromArray($this->unwrapEnvelope($data, 'country-tax', 'creating country tax'));
    }

    /**
     * Ändert ein steuerfreies Land (PUT /country-taxes/{id}).
     *
     * Billomat verlangt `country_code` auch hier; ohne das Feld antwortet es mit HTTP 400.
     */
    public function update(int $id, CountryTaxUpdateOptions $options): CountryTax
    {
        $data = $this->putJson("/country-taxes/{$id}", ['country-tax' => $options->toArray()]);

        return CountryTax::fromArray($this->unwrapEnvelope($data, 'country-tax', 'updating country tax'));
    }

    /**
     * Entfernt ein steuerfreies Land.
     */
    public function delete(int $id): void
    {
        $this->deleteVoid("/country-taxes/{$id}");
    }
}
