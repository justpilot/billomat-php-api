<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\CreditNoteItem;
use RuntimeException;

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

        $data = $this->getJson('/credit-note-items', $params);

        $itemsData = $data['credit-note-items']['credit-note-item'] ?? [];

        if (isset($itemsData['id'])) {
            $itemsData = [$itemsData];
        }

        if (!\is_array($itemsData) || [] === $itemsData) {
            return [];
        }

        /** @var list<CreditNoteItem> $items */
        $items = array_map(
            CreditNoteItem::fromArray(...),
            $itemsData,
        );

        return $items;
    }

    public function get(int $id): ?CreditNoteItem
    {
        $data = $this->getJsonOrNull("/credit-note-items/{$id}");

        if (null === $data) {
            return null;
        }

        $itemData = $data['credit-note-item'] ?? null;

        if (!\is_array($itemData)) {
            throw new RuntimeException('Unexpected response from Billomat when fetching credit note item.');
        }

        return CreditNoteItem::fromArray($itemData);
    }

    public function create(int $creditNoteId, CreditNoteItemCreateOptions $options): CreditNoteItem
    {
        $body = $options->toArray();
        $body['credit_note_id'] = $creditNoteId;

        $payload = ['credit-note-item' => $body];

        $data = $this->postJson('/credit-note-items', $payload);

        $itemData = $data['credit-note-item'] ?? null;

        if (!\is_array($itemData)) {
            throw new RuntimeException('Unexpected response from Billomat when creating credit note item.');
        }

        return CreditNoteItem::fromArray($itemData);
    }

    public function update(int $id, CreditNoteItemCreateOptions $options): CreditNoteItem
    {
        $payload = ['credit-note-item' => $options->toArray()];

        $data = $this->putJson("/credit-note-items/{$id}", $payload);

        $itemData = $data['credit-note-item'] ?? null;

        if (!\is_array($itemData)) {
            throw new RuntimeException('Unexpected response from Billomat when updating credit note item.');
        }

        return CreditNoteItem::fromArray($itemData);
    }

    public function delete(int $id): bool
    {
        $this->deleteVoid("/credit-note-items/{$id}");

        return true;
    }
}
