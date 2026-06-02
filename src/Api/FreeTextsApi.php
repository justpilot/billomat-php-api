<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\FreeText;

/**
 * Read-only API-Wrapper für Freitext-Bausteine.
 *
 * Doku: https://www.billomat.com/en/api/settings/free-texts/
 */
final class FreeTextsApi extends AbstractApi
{
    /**
     * @param array<string, scalar|array|null> $filters
     *
     * @return list<FreeText>
     */
    public function list(array $filters = []): array
    {
        $data = $this->getJson('/free-texts', $filters);

        $node = $data['free-texts']['free-text'] ?? [];

        if (null === $node || [] === $node) {
            return [];
        }

        if (\is_array($node) && array_is_list($node)) {
            $rows = $node;
        } elseif (\is_array($node)) {
            $rows = [$node];
        } else {
            $rows = [];
        }

        /** @var list<FreeText> $models */
        $models = array_map(FreeText::fromArray(...), $rows);

        return $models;
    }

    public function get(int $id): ?FreeText
    {
        $data = $this->getJsonOrNull("/free-texts/{$id}");

        if (null === $data) {
            return null;
        }

        $row = $data['free-text'] ?? null;
        if (!\is_array($row)) {
            return null;
        }

        return FreeText::fromArray($row);
    }
}
