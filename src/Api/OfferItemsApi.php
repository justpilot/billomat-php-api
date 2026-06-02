<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\OfferItem;

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

        return $this->listResource('/offer-items', 'offer-items', 'offer-item', OfferItem::fromArray(...), $params);
    }

    public function get(int $id): ?OfferItem
    {
        $data = $this->getJsonOrNull("/offer-items/{$id}");

        if (null === $data) {
            return null;
        }

        return OfferItem::fromArray($this->unwrapEnvelope($data, 'offer-item', 'fetching offer item'));
    }

    public function create(int $offerId, OfferItemCreateOptions $options): OfferItem
    {
        $body = $options->toArray();
        $body['offer_id'] = $offerId;

        $payload = ['offer-item' => $body];

        $data = $this->postJson('/offer-items', $payload);

        return OfferItem::fromArray($this->unwrapEnvelope($data, 'offer-item', 'creating offer item'));
    }

    public function update(int $id, OfferItemCreateOptions $options): OfferItem
    {
        $payload = ['offer-item' => $options->toArray()];

        $data = $this->putJson("/offer-items/{$id}", $payload);

        return OfferItem::fromArray($this->unwrapEnvelope($data, 'offer-item', 'updating offer item'));
    }

    public function delete(int $id): bool
    {
        $this->deleteVoid("/offer-items/{$id}");

        return true;
    }
}
