<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Generator;
use Justpilot\Billomat\Model\FreeText;
use Justpilot\Billomat\Pagination\Page;

/**
 * Read-only API-Wrapper für Freitext-Bausteine.
 *
 * Doku: https://www.billomat.com/en/api/settings/free-texts/
 */
final class FreeTextsApi extends AbstractApi
{
    /**
     * @param array<string, scalar|array|null> $filters
     *
     * @return list<FreeText>
     */
    public function list(array $filters = []): array
    {
        return $this->listResource('/free-texts', 'free-texts', 'free-text', FreeText::fromArray(...), $filters);
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
     * @return Page<FreeText>
     */
    public function listPage(array $filters = []): Page
    {
        return $this->listResourcePage('/free-texts', 'free-texts', 'free-text', FreeText::fromArray(...), $filters);
    }

    /**
     * Iteriert lazy durch alle FreeText und yieldet sie einzeln.
     *
     * Holt seitenweise pro {@code $pageSize}-Items und stoppt automatisch,
     * sobald die letzte Seite erreicht ist (analog `auto_paging_iter()` im
     * Stripe-SDK). Filter werden bei jeder Page-Anfrage mitgesendet; `page`
     * und `per_page` darin werden überschrieben.
     *
     * @param array<string, scalar|array|null> $filters
     *
     * @return Generator<int, FreeText>
     */
    public function iterateAll(array $filters = [], int $pageSize = 100): Generator
    {
        yield from $this->iterateResource('/free-texts', 'free-texts', 'free-text', FreeText::fromArray(...), $filters, $pageSize);
    }

    public function get(int $id): ?FreeText
    {
        $data = $this->getJsonOrNull("/free-texts/{$id}");

        if (null === $data) {
            return null;
        }

        $row = $data['free-text'] ?? null;
        if (!\is_array($row)) {
            return null;
        }

        return FreeText::fromArray($row);
    }
}
