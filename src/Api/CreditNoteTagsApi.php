<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\CreditNoteTag;
use Justpilot\Billomat\Model\CreditNoteTagCloudEntry;

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
        return $this->listResource('/credit-note-tags', 'credit-note-tags', 'credit-note-tag', CreditNoteTag::fromArray(...), ['credit_note_id' => $creditNoteId]);
    }

    /**
     * @return list<CreditNoteTagCloudEntry>
     */
    public function cloud(): array
    {
        return $this->listResource('/credit-note-tags', 'credit-note-tags', 'tag', CreditNoteTagCloudEntry::fromArray(...));
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

        return CreditNoteTag::fromArray($this->unwrapEnvelope($data, 'credit-note-tag', 'creating credit note tag'));
    }

    public function delete(int $id): bool
    {
        $this->deleteVoid("/credit-note-tags/{$id}");

        return true;
    }
}
