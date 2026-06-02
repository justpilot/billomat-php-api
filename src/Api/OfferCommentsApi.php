<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\Enum\OfferCommentActionKey;
use Justpilot\Billomat\Model\OfferComment;
use RuntimeException;

/**
 * API-Wrapper für Angebotskommentare (Offer Comments).
 *
 * Endpoints:
 *  - GET    /offer-comments?offer_id={id}
 *  - GET    /offer-comments/{id}
 *  - POST   /offer-comments
 *  - DELETE /offer-comments/{id}
 *
 * Doku: https://www.billomat.com/en/api/estimates/comments/
 */
final class OfferCommentsApi extends AbstractApi
{
    /**
     * @param list<OfferCommentActionKey>|null $actionKeys
     *
     * @return list<OfferComment>
     */
    public function listByOffer(int $offerId, ?array $actionKeys = null): array
    {
        $query = ['offer_id' => $offerId];

        if (null !== $actionKeys && [] !== $actionKeys) {
            $query['actionkey'] = implode(',', array_map(
                static fn (OfferCommentActionKey $a): string => $a->value,
                $actionKeys,
            ));
        }

        $data = $this->getJson('/offer-comments', $query);

        $root = $data['offer-comments'] ?? null;
        if (!\is_array($root)) {
            return [];
        }

        $rows = $root['offer-comment'] ?? [];

        if ([] === $rows || null === $rows) {
            return [];
        }

        if (isset($rows['id'])) {
            $rows = [$rows];
        }

        if (!\is_array($rows)) {
            return [];
        }

        /** @var list<OfferComment> $comments */
        $comments = array_map(
            OfferComment::fromArray(...),
            $rows,
        );

        return $comments;
    }

    public function get(int $id): ?OfferComment
    {
        $data = $this->getJsonOrNull("/offer-comments/{$id}");

        if (null === $data) {
            return null;
        }

        $row = $data['offer-comment'] ?? null;
        if (!\is_array($row)) {
            return null;
        }

        return OfferComment::fromArray($row);
    }

    public function create(OfferCommentCreateOptions $options): OfferComment
    {
        $payload = ['offer-comment' => $options->toArray()];

        $data = $this->postJson('/offer-comments', $payload);

        $row = $data['offer-comment'] ?? null;
        if (!\is_array($row)) {
            throw new RuntimeException('Unexpected response from Billomat when creating offer comment.');
        }

        return OfferComment::fromArray($row);
    }

    public function delete(int $id): bool
    {
        $this->deleteVoid("/offer-comments/{$id}");

        return true;
    }
}
