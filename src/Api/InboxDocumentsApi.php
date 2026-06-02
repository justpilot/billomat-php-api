<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\InboxDocument;

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
