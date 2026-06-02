<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\ClientTag;
use Justpilot\Billomat\Model\ClientTagCloudEntry;

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
        return $this->listResource('/client-tags', 'client-tags', 'client-tag', ClientTag::fromArray(...), ['client_id' => $clientId]);
    }

    /**
     * @return list<ClientTagCloudEntry>
     */
    public function cloud(): array
    {
        return $this->listResource('/client-tags', 'client-tags', 'tag', ClientTagCloudEntry::fromArray(...));
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

        return ClientTag::fromArray($this->unwrapEnvelope($data, 'client-tag', 'creating client tag'));
    }

    public function delete(int $id): bool
    {
        $this->deleteVoid("/client-tags/{$id}");

        return true;
    }
}
