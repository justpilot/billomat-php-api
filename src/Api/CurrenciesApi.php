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
        $data = $this->getJson('/currencies');

        $node = $data['currencies']['currency'] ?? [];

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

        /** @var list<Currency> $models */
        $models = array_map(Currency::fromArray(...), $rows);

        return $models;
    }
}
