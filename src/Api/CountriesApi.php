<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\Country;

/**
 * Read-only API-Wrapper für die Länderliste.
 *
 * Doku: https://www.billomat.com/en/api/countries/
 */
final class CountriesApi extends AbstractApi
{
    /**
     * @return list<Country>
     */
    public function list(): array
    {
        $data = $this->getJson('/countries');

        $node = $data['countries']['country'] ?? [];

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

        /** @var list<Country> $models */
        $models = array_map(Country::fromArray(...), $rows);

        return $models;
    }
}
