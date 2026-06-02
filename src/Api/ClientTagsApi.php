<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\ClientTag;
use Justpilot\Billomat\Model\ClientTagCloudEntry;
use RuntimeException;

/**
 * API-Wrapper für Client-Tags.
 */
final class ClientTagsApi extends AbstractApi
{
    /**
     * @return list<ClientTag>
     */
    public function listByClient(int $clientId): array
    {
        $data = $this->getJson('/client-tags', ['client_id' => $clientId]);

        $root = $data['client-tags'] ?? null;
        if (!\is_array($root)) {
            return [];
        }

        $rows = $root['client-tag'] ?? [];
        if ([] === $rows || null === $rows) {
            return [];
        }

        if (isset($rows['id'])) {
            $rows = [$rows];
        }

        if (!\is_array($rows)) {
            return [];
        }

        /** @var list<ClientTag> $tags */
        $tags = array_map(ClientTag::fromArray(...), $rows);

        return $tags;
    }

    /**
     * @return list<ClientTagCloudEntry>
     */
    public function cloud(): array
    {
        $data = $this->getJson('/client-tags');

        $root = $data['client-tags'] ?? null;
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

        /** @var list<ClientTagCloudEntry> $tags */
        $tags = array_map(ClientTagCloudEntry::fromArray(...), $rows);

        return $tags;
    }

    public function get(int $id): ?ClientTag
    {
        $data = $this->getJsonOrNull("/client-tags/{$id}");

        if (null === $data) {
            return null;
        }

        $row = $data['client-tag'] ?? null;
        if (!\is_array($row)) {
            return null;
        }

        return ClientTag::fromArray($row);
    }

    public function create(ClientTagCreateOptions $options): ClientTag
    {
        $payload = ['client-tag' => $options->toArray()];

        $data = $this->postJson('/client-tags', $payload);

        $row = $data['client-tag'] ?? null;
        if (!\is_array($row)) {
            throw new RuntimeException('Unexpected response from Billomat when creating client tag.');
        }

        return ClientTag::fromArray($row);
    }

    public function delete(int $id): bool
    {
        $this->deleteVoid("/client-tags/{$id}");

        return true;
    }
}
