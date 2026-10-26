<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Generator;
use Justpilot\Billomat\Exception\NotFoundException;
use Justpilot\Billomat\Model\InboxDocument;
use Justpilot\Billomat\Pagination\Page;
use NoDiscard;

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
     * @param array<string, scalar|array<scalar|null>|null> $filters
     *
     * @return list<InboxDocument>
     */
    #[NoDiscard]
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
     * @param array<string, scalar|array<scalar|null>|null> $filters
     *
     * @return Page<InboxDocument>
     */
    #[NoDiscard]
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
     * @param array<string, scalar|array<scalar|null>|null> $filters
     *
     * @return Generator<int, InboxDocument>
     */
    #[NoDiscard]
    public function iterateAll(array $filters = [], int $pageSize = 100): Generator
    {
        yield from $this->iterateResource('/inbox-documents', 'inbox-documents', 'inbox-document', InboxDocument::fromArray(...), $filters, $pageSize);
    }

    #[NoDiscard]
    public function get(int $id): ?InboxDocument
    {
        return $this->getResource("/inbox-documents/{$id}", 'inbox-document', InboxDocument::fromArray(...), 'fetching inbox document');
    }

    /**
     * Wie {@see get()}, wirft aber eine {@see NotFoundException} statt null zurückzugeben.
     *
     * @throws NotFoundException wenn es den Datensatz nicht gibt (404)
     */
    #[NoDiscard]
    public function getOrFail(int $id): InboxDocument
    {
        return $this->get($id) ?? throw $this->notFound("/inbox-documents/{$id}");
    }

    public function create(InboxDocumentCreateOptions $options): InboxDocument
    {
        $payload = ['inbox-document' => $options->toArray()];

        $data = $this->postJson('/inbox-documents', $payload);

        return InboxDocument::fromArray($this->unwrapEnvelope($data, 'inbox-document', 'creating inbox document'));
    }

    public function delete(int $id): void
    {
        $this->deleteVoid("/inbox-documents/{$id}");
    }
}
