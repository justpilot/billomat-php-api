<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\DeliveryNoteItem;

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

        return $this->listResource('/delivery-note-items', 'delivery-note-items', 'delivery-note-item', DeliveryNoteItem::fromArray(...), $params);
    }

    public function get(int $id): ?DeliveryNoteItem
    {
        $data = $this->getJsonOrNull("/delivery-note-items/{$id}");

        if (null === $data) {
            return null;
        }

        return DeliveryNoteItem::fromArray($this->unwrapEnvelope($data, 'delivery-note-item', 'fetching delivery note item'));
    }

    public function create(int $deliveryNoteId, DeliveryNoteItemCreateOptions $options): DeliveryNoteItem
    {
        $body = $options->toArray();
        $body['delivery_note_id'] = $deliveryNoteId;

        $payload = ['delivery-note-item' => $body];

        $data = $this->postJson('/delivery-note-items', $payload);

        return DeliveryNoteItem::fromArray($this->unwrapEnvelope($data, 'delivery-note-item', 'creating delivery note item'));
    }

    public function update(int $id, DeliveryNoteItemCreateOptions $options): DeliveryNoteItem
    {
        $payload = ['delivery-note-item' => $options->toArray()];

        $data = $this->putJson("/delivery-note-items/{$id}", $payload);

        return DeliveryNoteItem::fromArray($this->unwrapEnvelope($data, 'delivery-note-item', 'updating delivery note item'));
    }

    public function delete(int $id): bool
    {
        $this->deleteVoid("/delivery-note-items/{$id}");

        return true;
    }
}
