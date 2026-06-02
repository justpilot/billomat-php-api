<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\ConfirmationTag;
use Justpilot\Billomat\Model\ConfirmationTagCloudEntry;

/**
 * API-Wrapper für Confirmation-Tags.
 *
 * Doku: https://www.billomat.com/en/api/confirmations/tags/
 */
final class ConfirmationTagsApi extends AbstractApi
{
    /**
     * @return list<ConfirmationTag>
     */
    public function listByConfirmation(int $confirmationId): array
    {
        return $this->listResource('/confirmation-tags', 'confirmation-tags', 'confirmation-tag', ConfirmationTag::fromArray(...), ['confirmation_id' => $confirmationId]);
    }

    /**
     * @return list<ConfirmationTagCloudEntry>
     */
    public function cloud(): array
    {
        return $this->listResource('/confirmation-tags', 'confirmation-tags', 'tag', ConfirmationTagCloudEntry::fromArray(...));
    }

    public function get(int $id): ?ConfirmationTag
    {
        $data = $this->getJsonOrNull("/confirmation-tags/{$id}");

        if (null === $data) {
            return null;
        }

        $row = $data['confirmation-tag'] ?? null;
        if (!\is_array($row)) {
            return null;
        }

        return ConfirmationTag::fromArray($row);
    }

    public function create(ConfirmationTagCreateOptions $options): ConfirmationTag
    {
        $payload = ['confirmation-tag' => $options->toArray()];

        $data = $this->postJson('/confirmation-tags', $payload);

        return ConfirmationTag::fromArray($this->unwrapEnvelope($data, 'confirmation-tag', 'creating confirmation tag'));
    }

    public function delete(int $id): bool
    {
        $this->deleteVoid("/confirmation-tags/{$id}");

        return true;
    }
}
