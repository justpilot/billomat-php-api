<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\CreditNoteItem;

/**
 * API-Wrapper für Credit-Note-Items.
 */
final class CreditNoteItemsApi extends AbstractApi
{
    /**
     * @param array<string, scalar|array|null> $query
     *
     * @return list<CreditNoteItem>
     */
    public function listByCreditNote(int $creditNoteId, array $query = []): array
    {
        $params = array_merge(['credit_note_id' => $creditNoteId], $query);

        return $this->listResource('/credit-note-items', 'credit-note-items', 'credit-note-item', CreditNoteItem::fromArray(...), $params);
    }

    public function get(int $id): ?CreditNoteItem
    {
        $data = $this->getJsonOrNull("/credit-note-items/{$id}");

        if (null === $data) {
            return null;
        }

        return CreditNoteItem::fromArray($this->unwrapEnvelope($data, 'credit-note-item', 'fetching credit note item'));
    }

    public function create(int $creditNoteId, CreditNoteItemCreateOptions $options): CreditNoteItem
    {
        $body = $options->toArray();
        $body['credit_note_id'] = $creditNoteId;

        $payload = ['credit-note-item' => $body];

        $data = $this->postJson('/credit-note-items', $payload);

        return CreditNoteItem::fromArray($this->unwrapEnvelope($data, 'credit-note-item', 'creating credit note item'));
    }

    public function update(int $id, CreditNoteItemCreateOptions $options): CreditNoteItem
    {
        $payload = ['credit-note-item' => $options->toArray()];

        $data = $this->putJson("/credit-note-items/{$id}", $payload);

        return CreditNoteItem::fromArray($this->unwrapEnvelope($data, 'credit-note-item', 'updating credit note item'));
    }

    public function delete(int $id): bool
    {
        $this->deleteVoid("/credit-note-items/{$id}");

        return true;
    }
}
