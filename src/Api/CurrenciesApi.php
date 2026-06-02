<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\Currency;

/**
 * Read-only API-Wrapper für die Währungsliste.
 *
 * Doku: https://www.billomat.com/en/api/currencies/
 */
final class CurrenciesApi extends AbstractApi
{
    /**
     * @return list<Currency>
     */
    public function list(): array
    {
        return $this->listResource('/currencies', 'currencies', 'currency', Currency::fromArray(...));
    }
}
