<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\DeliveryNoteTag;
use Justpilot\Billomat\Model\DeliveryNoteTagCloudEntry;

/**
 * API-Wrapper für Delivery-Note-Tags.
 */
final class DeliveryNoteTagsApi extends AbstractApi
{
    /**
     * @return list<DeliveryNoteTag>
     */
    public function listByDeliveryNote(int $deliveryNoteId): array
    {
        return $this->listResource('/delivery-note-tags', 'delivery-note-tags', 'delivery-note-tag', DeliveryNoteTag::fromArray(...), ['delivery_note_id' => $deliveryNoteId]);
    }

    /**
     * @return list<DeliveryNoteTagCloudEntry>
     */
    public function cloud(): array
    {
        return $this->listResource('/delivery-note-tags', 'delivery-note-tags', 'tag', DeliveryNoteTagCloudEntry::fromArray(...));
    }

    public function get(int $id): ?DeliveryNoteTag
    {
        $data = $this->getJsonOrNull("/delivery-note-tags/{$id}");

        if (null === $data) {
            return null;
        }

        $row = $data['delivery-note-tag'] ?? null;
        if (!\is_array($row)) {
            return null;
        }

        return DeliveryNoteTag::fromArray($row);
    }

    public function create(DeliveryNoteTagCreateOptions $options): DeliveryNoteTag
    {
        $payload = ['delivery-note-tag' => $options->toArray()];

        $data = $this->postJson('/delivery-note-tags', $payload);

        return DeliveryNoteTag::fromArray($this->unwrapEnvelope($data, 'delivery-note-tag', 'creating delivery note tag'));
    }

    public function delete(int $id): bool
    {
        $this->deleteVoid("/delivery-note-tags/{$id}");

        return true;
    }
}
