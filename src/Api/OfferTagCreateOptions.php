<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

/**
 * Payload für POST /offer-tags.
 *
 * Doku: https://www.billomat.com/en/api/estimates/tags/
 */
final class OfferTagCreateOptions
{
    public function __construct(
        public int $offerId,
        public string $name,
    ) {
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'offer_id' => $this->offerId,
            'name' => $this->name,
        ];
    }
}
