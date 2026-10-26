<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Generator;
use Justpilot\Billomat\Exception\NotFoundException;
use Justpilot\Billomat\Model\FreeText;
use Justpilot\Billomat\Pagination\Page;
use NoDiscard;

/**
 * Read-only API-Wrapper für Freitext-Bausteine.
 *
 * Doku: https://www.billomat.com/en/api/settings/free-texts/
 */
final class FreeTextsApi extends AbstractApi
{
    /**
     * @param array<string, scalar|array<scalar|null>|null> $filters
     *
     * @return list<FreeText>
     */
    #[NoDiscard]
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
     * @param array<string, scalar|array<scalar|null>|null> $filters
     *
     * @return Page<FreeText>
     */
    #[NoDiscard]
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
     * @param array<string, scalar|array<scalar|null>|null> $filters
     *
     * @return Generator<int, FreeText>
     */
    #[NoDiscard]
    public function iterateAll(array $filters = [], int $pageSize = 100): Generator
    {
        yield from $this->iterateResource('/free-texts', 'free-texts', 'free-text', FreeText::fromArray(...), $filters, $pageSize);
    }

    #[NoDiscard]
    public function get(int $id): ?FreeText
    {
        return $this->getResource("/free-texts/{$id}", 'free-text', FreeText::fromArray(...), 'fetching free text');
    }

    /**
     * Wie {@see get()}, wirft aber eine {@see NotFoundException} statt null zurückzugeben.
     *
     * @throws NotFoundException wenn es den Datensatz nicht gibt (404)
     */
    #[NoDiscard]
    public function getOrFail(int $id): FreeText
    {
        return $this->get($id) ?? throw $this->notFound("/free-texts/{$id}");
    }
}
