<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\ConfirmationItem;
use RuntimeException;

/**
 * API-Wrapper für Confirmation-Items.
 *
 * Endpoints:
 *  - GET    /confirmation-items?confirmation_id={id}
 *  - GET    /confirmation-items/{id}
 *  - POST   /confirmation-items
 *  - PUT    /confirmation-items/{id}
 *  - DELETE /confirmation-items/{id}
 *
 * Doku: https://www.billomat.com/en/api/confirmations/items/
 */
final class ConfirmationItemsApi extends AbstractApi
{
    /**
     * @param array<string, scalar|array|null> $query
     *
     * @return list<ConfirmationItem>
     */
    public function listByConfirmation(int $confirmationId, array $query = []): array
    {
        $params = array_merge(['confirmation_id' => $confirmationId], $query);

        $data = $this->getJson('/confirmation-items', $params);

        $itemsData = $data['confirmation-items']['confirmation-item'] ?? [];

        if (isset($itemsData['id'])) {
            $itemsData = [$itemsData];
        }

        if (!\is_array($itemsData) || [] === $itemsData) {
            return [];
        }

        /** @var list<ConfirmationItem> $items */
        $items = array_map(
            ConfirmationItem::fromArray(...),
            $itemsData,
        );

        return $items;
    }

    public function get(int $id): ?ConfirmationItem
    {
        $data = $this->getJsonOrNull("/confirmation-items/{$id}");

        if (null === $data) {
            return null;
        }

        $itemData = $data['confirmation-item'] ?? null;

        if (!\is_array($itemData)) {
            throw new RuntimeException('Unexpected response from Billomat when fetching confirmation item.');
        }

        return ConfirmationItem::fromArray($itemData);
    }

    public function create(int $confirmationId, ConfirmationItemCreateOptions $options): ConfirmationItem
    {
        $body = $options->toArray();
        $body['confirmation_id'] = $confirmationId;

        $payload = ['confirmation-item' => $body];

        $data = $this->postJson('/confirmation-items', $payload);

        $itemData = $data['confirmation-item'] ?? null;

        if (!\is_array($itemData)) {
            throw new RuntimeException('Unexpected response from Billomat when creating confirmation item.');
        }

        return ConfirmationItem::fromArray($itemData);
    }

    public function update(int $id, ConfirmationItemCreateOptions $options): ConfirmationItem
    {
        $payload = ['confirmation-item' => $options->toArray()];

        $data = $this->putJson("/confirmation-items/{$id}", $payload);

        $itemData = $data['confirmation-item'] ?? null;

        if (!\is_array($itemData)) {
            throw new RuntimeException('Unexpected response from Billomat when updating confirmation item.');
        }

        return ConfirmationItem::fromArray($itemData);
    }

    public function delete(int $id): bool
    {
        $this->deleteVoid("/confirmation-items/{$id}");

        return true;
    }
}
