<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\ConfirmationItem;

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

        return $this->listResource('/confirmation-items', 'confirmation-items', 'confirmation-item', ConfirmationItem::fromArray(...), $params);
    }

    public function get(int $id): ?ConfirmationItem
    {
        $data = $this->getJsonOrNull("/confirmation-items/{$id}");

        if (null === $data) {
            return null;
        }

        return ConfirmationItem::fromArray($this->unwrapEnvelope($data, 'confirmation-item', 'fetching confirmation item'));
    }

    public function create(int $confirmationId, ConfirmationItemCreateOptions $options): ConfirmationItem
    {
        $body = $options->toArray();
        $body['confirmation_id'] = $confirmationId;

        $payload = ['confirmation-item' => $body];

        $data = $this->postJson('/confirmation-items', $payload);

        return ConfirmationItem::fromArray($this->unwrapEnvelope($data, 'confirmation-item', 'creating confirmation item'));
    }

    public function update(int $id, ConfirmationItemCreateOptions $options): ConfirmationItem
    {
        $payload = ['confirmation-item' => $options->toArray()];

        $data = $this->putJson("/confirmation-items/{$id}", $payload);

        return ConfirmationItem::fromArray($this->unwrapEnvelope($data, 'confirmation-item', 'updating confirmation item'));
    }

    public function delete(int $id): bool
    {
        $this->deleteVoid("/confirmation-items/{$id}");

        return true;
    }
}
