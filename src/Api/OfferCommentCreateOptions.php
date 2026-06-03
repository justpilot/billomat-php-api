<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\Enum\OfferCommentActionKey;

/**
 * Payload für POST /offer-comments.
 *
 * Doku: https://www.billomat.com/en/api/estimates/comments/
 */
final class OfferCommentCreateOptions
{
    public ?OfferCommentActionKey $actionkey = null;

    /**
     * Sichtbarkeit für die Aktivitäten-Liste des Kunden. Default laut Billomat: false.
     */
    public ?bool $public = null;

    public function __construct(
        public int $offerId,
        public string $comment,
    ) {
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        $data = [
            'offer_id' => $this->offerId,
            'comment' => $this->comment,
            'actionkey' => $this->actionkey?->value,
            'public' => $this->public,
        ];

        return array_filter($data, static fn (mixed $v): bool => null !== $v);
    }
}
