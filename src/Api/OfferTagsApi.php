<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\OfferTag;
use Justpilot\Billomat\Model\OfferTagCloudEntry;
use RuntimeException;

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
        $data = $this->getJson('/offer-tags', ['offer_id' => $offerId]);

        $root = $data['offer-tags'] ?? null;
        if (!\is_array($root)) {
            return [];
        }

        $rows = $root['offer-tag'] ?? [];
        if ([] === $rows || null === $rows) {
            return [];
        }

        if (isset($rows['id'])) {
            $rows = [$rows];
        }

        if (!\is_array($rows)) {
            return [];
        }

        /** @var list<OfferTag> $tags */
        $tags = array_map(OfferTag::fromArray(...), $rows);

        return $tags;
    }

    /**
     * Tag-Cloud: alle Tags aggregiert mit Häufigkeit.
     *
     * @return list<OfferTagCloudEntry>
     */
    public function cloud(): array
    {
        $data = $this->getJson('/offer-tags');

        $root = $data['offer-tags'] ?? null;
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

        /** @var list<OfferTagCloudEntry> $tags */
        $tags = array_map(OfferTagCloudEntry::fromArray(...), $rows);

        return $tags;
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

        $row = $data['offer-tag'] ?? null;
        if (!\is_array($row)) {
            throw new RuntimeException('Unexpected response from Billomat when creating offer tag.');
        }

        return OfferTag::fromArray($row);
    }

    public function delete(int $id): bool
    {
        $this->deleteVoid("/offer-tags/{$id}");

        return true;
    }
}
