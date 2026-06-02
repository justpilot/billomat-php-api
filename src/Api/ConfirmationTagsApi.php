<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\ConfirmationTag;
use Justpilot\Billomat\Model\ConfirmationTagCloudEntry;
use RuntimeException;

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
        $data = $this->getJson('/confirmation-tags', ['confirmation_id' => $confirmationId]);

        $root = $data['confirmation-tags'] ?? null;
        if (!\is_array($root)) {
            return [];
        }

        $rows = $root['confirmation-tag'] ?? [];
        if ([] === $rows || null === $rows) {
            return [];
        }

        if (isset($rows['id'])) {
            $rows = [$rows];
        }

        if (!\is_array($rows)) {
            return [];
        }

        /** @var list<ConfirmationTag> $tags */
        $tags = array_map(ConfirmationTag::fromArray(...), $rows);

        return $tags;
    }

    /**
     * @return list<ConfirmationTagCloudEntry>
     */
    public function cloud(): array
    {
        $data = $this->getJson('/confirmation-tags');

        $root = $data['confirmation-tags'] ?? null;
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

        /** @var list<ConfirmationTagCloudEntry> $tags */
        $tags = array_map(ConfirmationTagCloudEntry::fromArray(...), $rows);

        return $tags;
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

        $row = $data['confirmation-tag'] ?? null;
        if (!\is_array($row)) {
            throw new RuntimeException('Unexpected response from Billomat when creating confirmation tag.');
        }

        return ConfirmationTag::fromArray($row);
    }

    public function delete(int $id): bool
    {
        $this->deleteVoid("/confirmation-tags/{$id}");

        return true;
    }
}
