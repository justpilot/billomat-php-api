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
        return $this->listResource('/countries', 'countries', 'country', Country::fromArray(...));
    }
}
