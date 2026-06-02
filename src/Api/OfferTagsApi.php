<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\OfferTag;
use Justpilot\Billomat\Model\OfferTagCloudEntry;

/**
 * API-Wrapper für Angebots-Schlagworte (Offer Tags).
 *
 * Endpoints:
 *  - GET    /offer-tags?offer_id={id} (Tags eines Angebots)
 *  - GET    /offer-tags                (Tag-Cloud, aggregiert)
 *  - GET    /offer-tags/{id}
 *  - POST   /offer-tags
 *  - DELETE /offer-tags/{id}
 *
 * Doku: https://www.billomat.com/en/api/estimates/tags/
 */
final class OfferTagsApi extends AbstractApi
{
    /**
     * @return list<OfferTag>
     */
    public function listByOffer(int $offerId): array
    {
        return $this->listResource('/offer-tags', 'offer-tags', 'offer-tag', OfferTag::fromArray(...), ['offer_id' => $offerId]);
    }

    /**
     * Tag-Cloud: alle Tags aggregiert mit Häufigkeit.
     *
     * @return list<OfferTagCloudEntry>
     */
    public function cloud(): array
    {
        return $this->listResource('/offer-tags', 'offer-tags', 'tag', OfferTagCloudEntry::fromArray(...));
    }

    public function get(int $id): ?OfferTag
    {
        $data = $this->getJsonOrNull("/offer-tags/{$id}");

        if (null === $data) {
            return null;
        }

        $row = $data['offer-tag'] ?? null;
        if (!\is_array($row)) {
            return null;
        }

        return OfferTag::fromArray($row);
    }

    public function create(OfferTagCreateOptions $options): OfferTag
    {
        $payload = ['offer-tag' => $options->toArray()];

        $data = $this->postJson('/offer-tags', $payload);

        return OfferTag::fromArray($this->unwrapEnvelope($data, 'offer-tag', 'creating offer tag'));
    }

    public function delete(int $id): bool
    {
        $this->deleteVoid("/offer-tags/{$id}");

        return true;
    }
}
