<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\DeliveryNoteTag;
use Justpilot\Billomat\Model\DeliveryNoteTagCloudEntry;
use RuntimeException;

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
        $data = $this->getJson('/delivery-note-tags', ['delivery_note_id' => $deliveryNoteId]);

        $root = $data['delivery-note-tags'] ?? null;
        if (!\is_array($root)) {
            return [];
        }

        $rows = $root['delivery-note-tag'] ?? [];
        if ([] === $rows || null === $rows) {
            return [];
        }

        if (isset($rows['id'])) {
            $rows = [$rows];
        }

        if (!\is_array($rows)) {
            return [];
        }

        /** @var list<DeliveryNoteTag> $tags */
        $tags = array_map(DeliveryNoteTag::fromArray(...), $rows);

        return $tags;
    }

    /**
     * @return list<DeliveryNoteTagCloudEntry>
     */
    public function cloud(): array
    {
        $data = $this->getJson('/delivery-note-tags');

        $root = $data['delivery-note-tags'] ?? null;
        if (!\is_array($root)) {
            return [];
        }

        $rows = $root['tag'] ?? [];
        if ([] === $rows || null === $rows) {
            return [];
        }

        if (isset($rows['name'])) {
            $rows = [$rows];
        }

        if (!\is_array($rows)) {
            return [];
        }

        /** @var list<DeliveryNoteTagCloudEntry> $tags */
        $tags = array_map(DeliveryNoteTagCloudEntry::fromArray(...), $rows);

        return $tags;
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

        $row = $data['delivery-note-tag'] ?? null;
        if (!\is_array($row)) {
            throw new RuntimeException('Unexpected response from Billomat when creating delivery note tag.');
        }

        return DeliveryNoteTag::fromArray($row);
    }

    public function delete(int $id): bool
    {
        $this->deleteVoid("/delivery-note-tags/{$id}");

        return true;
    }
}
