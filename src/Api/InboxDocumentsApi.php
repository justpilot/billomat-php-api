<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\InboxDocument;
use RuntimeException;

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
        $data = $this->getJson('/inbox-documents', $filters);

        $node = $data['inbox-documents']['inbox-document'] ?? [];

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

        /** @var list<InboxDocument> $models */
        $models = array_map(
            InboxDocument::fromArray(...),
            $rows,
        );

        return $models;
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

        $created = $data['inbox-document'] ?? null;

        if (!\is_array($created)) {
            throw new RuntimeException('Unexpected response from Billomat when creating inbox document.');
        }

        return InboxDocument::fromArray($created);
    }

    public function delete(int $id): bool
    {
        $this->deleteVoid("/inbox-documents/{$id}");

        return true;
    }
}
