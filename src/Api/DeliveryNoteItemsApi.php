<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\DeliveryNoteItem;
use RuntimeException;

/**
 * API-Wrapper für Delivery-Note-Items.
 *
 * Doku: https://www.billomat.com/en/api/delivery-notes/items/
 */
final class DeliveryNoteItemsApi extends AbstractApi
{
    /**
     * @param array<string, scalar|array|null> $query
     *
     * @return list<DeliveryNoteItem>
     */
    public function listByDeliveryNote(int $deliveryNoteId, array $query = []): array
    {
        $params = array_merge(['delivery_note_id' => $deliveryNoteId], $query);

        $data = $this->getJson('/delivery-note-items', $params);

        $itemsData = $data['delivery-note-items']['delivery-note-item'] ?? [];

        if (isset($itemsData['id'])) {
            $itemsData = [$itemsData];
        }

        if (!\is_array($itemsData) || [] === $itemsData) {
            return [];
        }

        /** @var list<DeliveryNoteItem> $items */
        $items = array_map(
            DeliveryNoteItem::fromArray(...),
            $itemsData,
        );

        return $items;
    }

    public function get(int $id): ?DeliveryNoteItem
    {
        $data = $this->getJsonOrNull("/delivery-note-items/{$id}");

        if (null === $data) {
            return null;
        }

        $itemData = $data['delivery-note-item'] ?? null;

        if (!\is_array($itemData)) {
            throw new RuntimeException('Unexpected response from Billomat when fetching delivery note item.');
        }

        return DeliveryNoteItem::fromArray($itemData);
    }

    public function create(int $deliveryNoteId, DeliveryNoteItemCreateOptions $options): DeliveryNoteItem
    {
        $body = $options->toArray();
        $body['delivery_note_id'] = $deliveryNoteId;

        $payload = ['delivery-note-item' => $body];

        $data = $this->postJson('/delivery-note-items', $payload);

        $itemData = $data['delivery-note-item'] ?? null;

        if (!\is_array($itemData)) {
            throw new RuntimeException('Unexpected response from Billomat when creating delivery note item.');
        }

        return DeliveryNoteItem::fromArray($itemData);
    }

    public function update(int $id, DeliveryNoteItemCreateOptions $options): DeliveryNoteItem
    {
        $payload = ['delivery-note-item' => $options->toArray()];

        $data = $this->putJson("/delivery-note-items/{$id}", $payload);

        $itemData = $data['delivery-note-item'] ?? null;

        if (!\is_array($itemData)) {
            throw new RuntimeException('Unexpected response from Billomat when updating delivery note item.');
        }

        return DeliveryNoteItem::fromArray($itemData);
    }

    public function delete(int $id): bool
    {
        $this->deleteVoid("/delivery-note-items/{$id}");

        return true;
    }
}
