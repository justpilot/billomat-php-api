<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Generator;
use Justpilot\Billomat\Model\EmailTemplate;
use Justpilot\Billomat\Pagination\Page;

/**
 * Read-only API-Wrapper für E-Mail-Vorlagen.
 *
 * Doku: https://www.billomat.com/en/api/settings/email-templates/
 */
final class EmailTemplatesApi extends AbstractApi
{
    /**
     * @param array<string, scalar|array|null> $filters
     *
     * @return list<EmailTemplate>
     */
    public function list(array $filters = []): array
    {
        return $this->listResource('/email-templates', 'email-templates', 'email-template', EmailTemplate::fromArray(...), $filters);
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
     * @return Page<EmailTemplate>
     */
    public function listPage(array $filters = []): Page
    {
        return $this->listResourcePage('/email-templates', 'email-templates', 'email-template', EmailTemplate::fromArray(...), $filters);
    }

    /**
     * Iteriert lazy durch alle EmailTemplate und yieldet sie einzeln.
     *
     * Holt seitenweise pro {@code $pageSize}-Items und stoppt automatisch,
     * sobald die letzte Seite erreicht ist (analog `auto_paging_iter()` im
     * Stripe-SDK). Filter werden bei jeder Page-Anfrage mitgesendet; `page`
     * und `per_page` darin werden überschrieben.
     *
     * @param array<string, scalar|array|null> $filters
     *
     * @return Generator<int, EmailTemplate>
     */
    public function iterateAll(array $filters = [], int $pageSize = 100): Generator
    {
        yield from $this->iterateResource('/email-templates', 'email-templates', 'email-template', EmailTemplate::fromArray(...), $filters, $pageSize);
    }

    public function get(int $id): ?EmailTemplate
    {
        $data = $this->getJsonOrNull("/email-templates/{$id}");

        if (null === $data) {
            return null;
        }

        $row = $data['email-template'] ?? null;
        if (!\is_array($row)) {
            return null;
        }

        return EmailTemplate::fromArray($row);
    }
}
