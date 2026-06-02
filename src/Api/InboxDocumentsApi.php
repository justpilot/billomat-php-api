<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Generator;
use Justpilot\Billomat\Model\InboxDocument;
use Justpilot\Billomat\Pagination\Page;

/**
 * API-Wrapper für Posteingangs-Dokumente (Inbox Documents).
 *
 * Doku: https://www.billomat.com/en/api/inbox-documents/
 *
 * Endpoints:
 *  - GET    /inbox-documents
 *  - GET    /inbox-documents/{id}
 *  - POST   /inbox-documents
 *  - DELETE /inbox-documents/{id}
 */
final class InboxDocumentsApi extends AbstractApi
{
    /**
     * @param array<string, scalar|array|null> $filters
     *
     * @return list<InboxDocument>
     */
    public function list(array $filters = []): array
    {
        return $this->listResource('/inbox-documents', 'inbox-documents', 'inbox-document', InboxDocument::fromArray(...), $filters);
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
     * @return Page<InboxDocument>
     */
    public function listPage(array $filters = []): Page
    {
        return $this->listResourcePage('/inbox-documents', 'inbox-documents', 'inbox-document', InboxDocument::fromArray(...), $filters);
    }

    /**
     * Iteriert lazy durch alle Posteingangs-Dokumente und yieldet sie einzeln.
     *
     * Holt seitenweise pro {@code $pageSize}-Items und stoppt automatisch,
     * sobald die letzte Seite erreicht ist (analog `auto_paging_iter()` im
     * Stripe-SDK). Filter werden bei jeder Page-Anfrage mitgesendet; `page`
     * und `per_page` darin werden überschrieben.
     *
     * @param array<string, scalar|array|null> $filters
     *
     * @return Generator<int, InboxDocument>
     */
    public function iterateAll(array $filters = [], int $pageSize = 100): Generator
    {
        yield from $this->iterateResource('/inbox-documents', 'inbox-documents', 'inbox-document', InboxDocument::fromArray(...), $filters, $pageSize);
    }

    public function get(int $id): ?InboxDocument
    {
        $data = $this->getJsonOrNull("/inbox-documents/{$id}");

        if (null === $data) {
            return null;
        }

        $row = $data['inbox-document'] ?? null;

        if (!\is_array($row)) {
            return null;
        }

        return InboxDocument::fromArray($row);
    }

    public function create(InboxDocumentCreateOptions $options): InboxDocument
    {
        $payload = ['inbox-document' => $options->toArray()];

        $data = $this->postJson('/inbox-documents', $payload);

        return InboxDocument::fromArray($this->unwrapEnvelope($data, 'inbox-document', 'creating inbox document'));
    }

    public function delete(int $id): bool
    {
        $this->deleteVoid("/inbox-documents/{$id}");

        return true;
    }
}
