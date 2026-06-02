<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\OfferItem;
use RuntimeException;

/**
 * API-Wrapper für Angebotspositionen (Offer Items).
 *
 * Endpoints:
 *  - GET    /offer-items?offer_id={id}
 *  - GET    /offer-items/{id}
 *  - POST   /offer-items
 *  - PUT    /offer-items/{id}
 *  - DELETE /offer-items/{id}
 *
 * Doku: https://www.billomat.com/en/api/estimates/items/
 */
final class OfferItemsApi extends AbstractApi
{
    /**
     * @param array<string, scalar|array|null> $query
     *
     * @return list<OfferItem>
     */
    public function listByOffer(int $offerId, array $query = []): array
    {
        $params = array_merge(['offer_id' => $offerId], $query);

        $data = $this->getJson('/offer-items', $params);

        $itemsData = $data['offer-items']['offer-item'] ?? [];

        if (isset($itemsData['id'])) {
            $itemsData = [$itemsData];
        }

        if (!\is_array($itemsData) || [] === $itemsData) {
            return [];
        }

        /** @var list<OfferItem> $items */
        $items = array_map(
            OfferItem::fromArray(...),
            $itemsData,
        );

        return $items;
    }

    public function get(int $id): ?OfferItem
    {
        $data = $this->getJsonOrNull("/offer-items/{$id}");

        if (null === $data) {
            return null;
        }

        $itemData = $data['offer-item'] ?? null;

        if (!\is_array($itemData)) {
            throw new RuntimeException('Unexpected response from Billomat when fetching offer item.');
        }

        return OfferItem::fromArray($itemData);
    }

    public function create(int $offerId, OfferItemCreateOptions $options): OfferItem
    {
        $body = $options->toArray();
        $body['offer_id'] = $offerId;

        $payload = ['offer-item' => $body];

        $data = $this->postJson('/offer-items', $payload);

        $itemData = $data['offer-item'] ?? null;

        if (!\is_array($itemData)) {
            throw new RuntimeException('Unexpected response from Billomat when creating offer item.');
        }

        return OfferItem::fromArray($itemData);
    }

    public function update(int $id, OfferItemCreateOptions $options): OfferItem
    {
        $payload = ['offer-item' => $options->toArray()];

        $data = $this->putJson("/offer-items/{$id}", $payload);

        $itemData = $data['offer-item'] ?? null;

        if (!\is_array($itemData)) {
            throw new RuntimeException('Unexpected response from Billomat when updating offer item.');
        }

        return OfferItem::fromArray($itemData);
    }

    public function delete(int $id): bool
    {
        $this->deleteVoid("/offer-items/{$id}");

        return true;
    }
}
