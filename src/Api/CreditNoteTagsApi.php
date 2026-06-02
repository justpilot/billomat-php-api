<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\CreditNoteTag;
use Justpilot\Billomat\Model\CreditNoteTagCloudEntry;
use RuntimeException;

/**
 * API-Wrapper für Credit-Note-Tags.
 */
final class CreditNoteTagsApi extends AbstractApi
{
    /**
     * @return list<CreditNoteTag>
     */
    public function listByCreditNote(int $creditNoteId): array
    {
        $data = $this->getJson('/credit-note-tags', ['credit_note_id' => $creditNoteId]);

        $root = $data['credit-note-tags'] ?? null;
        if (!\is_array($root)) {
            return [];
        }

        $rows = $root['credit-note-tag'] ?? [];
        if ([] === $rows || null === $rows) {
            return [];
        }

        if (isset($rows['id'])) {
            $rows = [$rows];
        }

        if (!\is_array($rows)) {
            return [];
        }

        /** @var list<CreditNoteTag> $tags */
        $tags = array_map(CreditNoteTag::fromArray(...), $rows);

        return $tags;
    }

    /**
     * @return list<CreditNoteTagCloudEntry>
     */
    public function cloud(): array
    {
        $data = $this->getJson('/credit-note-tags');

        $root = $data['credit-note-tags'] ?? null;
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

        /** @var list<CreditNoteTagCloudEntry> $tags */
        $tags = array_map(CreditNoteTagCloudEntry::fromArray(...), $rows);

        return $tags;
    }

    public function get(int $id): ?CreditNoteTag
    {
        $data = $this->getJsonOrNull("/credit-note-tags/{$id}");

        if (null === $data) {
            return null;
        }

        $row = $data['credit-note-tag'] ?? null;
        if (!\is_array($row)) {
            return null;
        }

        return CreditNoteTag::fromArray($row);
    }

    public function create(CreditNoteTagCreateOptions $options): CreditNoteTag
    {
        $payload = ['credit-note-tag' => $options->toArray()];

        $data = $this->postJson('/credit-note-tags', $payload);

        $row = $data['credit-note-tag'] ?? null;
        if (!\is_array($row)) {
            throw new RuntimeException('Unexpected response from Billomat when creating credit note tag.');
        }

        return CreditNoteTag::fromArray($row);
    }

    public function delete(int $id): bool
    {
        $this->deleteVoid("/credit-note-tags/{$id}");

        return true;
    }
}
