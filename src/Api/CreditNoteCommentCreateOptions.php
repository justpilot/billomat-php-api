<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\Enum\CreditNoteCommentActionKey;

/**
 * Typisierter Payload für POST /credit-note-comments gemäß Billomat-API.
 *
 * Null-Werte bedeuten "nicht gesetzt" und werden beim Serialisieren ignoriert.
 *
 * Doku:
 * https://www.billomat.com/api/gutschriften/kommentare/
 *
 * Generiert aus tools/codegen/definitions/credit-note-comments.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final readonly class CreditNoteCommentCreateOptions implements WriteOptions
{
    public function __construct(
        /**
         * ID des Dokuments, an dem der Kommentar hängt.
         *
         * Billomat-Feld: credit_note_id
         * Typ: INT
         */
        public int $creditNoteId,
        /**
         * Kommentartext.
         *
         * Billomat-Feld: comment
         * Typ: ALNUM
         */
        public string $comment,
        /**
         * ActionKey als Enum, sofern bekannt.
         *
         * Beim Anlegen normalerweise nicht relevant – Billomat setzt den Wert
         * bei system-getriebenen Kommentaren selbst.
         *
         * Billomat-Feld: actionkey
         */
        public ?CreditNoteCommentActionKey $actionkey = null,
        /**
         * Sichtbarkeit für die Aktivitäten-Liste des Kunden.
         *
         * Billomat-Feld: public
         * Typ: BOOL
         * Default: 0
         */
        public ?bool $public = null,
    ) {
    }

    /** Kopie mit geändertem `creditNoteId`. */
    public function withCreditNoteId(int $creditNoteId): self
    {
        return clone($this, ['creditNoteId' => $creditNoteId]);
    }

    /** Kopie mit geändertem `comment`. */
    public function withComment(string $comment): self
    {
        return clone($this, ['comment' => $comment]);
    }

    /** Kopie mit geändertem `actionkey`. */
    public function withActionkey(?CreditNoteCommentActionKey $actionkey): self
    {
        return clone($this, ['actionkey' => $actionkey]);
    }

    /** Kopie mit geändertem `public`. */
    public function withPublic(?bool $public): self
    {
        return clone($this, ['public' => $public]);
    }

    /**
     * Serialisiert die Options in ein Billomat-Payload-Array.
     *
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'credit_note_id' => $this->creditNoteId,
            'comment' => $this->comment,
            'actionkey' => $this->actionkey?->value,
            'public' => $this->public,
        ], static fn (mixed $v): bool => null !== $v);
    }
}
