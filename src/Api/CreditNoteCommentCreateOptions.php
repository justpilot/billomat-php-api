<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\Enum\CreditNoteCommentActionKey;

/**
 * Payload für POST /credit-note-comments.
 */
final class CreditNoteCommentCreateOptions
{
    public ?CreditNoteCommentActionKey $actionkey = null;

    /**
     * Sichtbarkeit für die Aktivitäten-Liste des Kunden. Default laut Billomat: false.
     */
    public ?bool $public = null;

    public function __construct(
        public int $creditNoteId,
        public string $comment,
    ) {
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        $data = [
            'credit_note_id' => $this->creditNoteId,
            'comment' => $this->comment,
            'actionkey' => $this->actionkey?->value,
            'public' => $this->public,
        ];

        return array_filter($data, static fn (mixed $v): bool => null !== $v);
    }
}
