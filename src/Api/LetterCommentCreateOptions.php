<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\Enum\LetterCommentActionKey;

/**
 * Typisierter Payload für POST /letter-comments gemäß Billomat-API.
 *
 * Null-Werte bedeuten "nicht gesetzt" und werden beim Serialisieren ignoriert.
 *
 * Doku:
 * https://www.billomat.com/api/briefe/
 *
 * Generiert aus tools/codegen/definitions/letter-comments.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final readonly class LetterCommentCreateOptions implements WriteOptions
{
    public function __construct(
        /**
         * Kommentartext.
         *
         * Billomat-Feld: comment
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
        public ?LetterCommentActionKey $actionkey = null,
    ) {
    }

    /** Kopie mit geändertem `comment`. */
    public function withComment(string $comment): self
    {
        return clone($this, ['comment' => $comment]);
    }

    /** Kopie mit geändertem `actionkey`. */
    public function withActionkey(?LetterCommentActionKey $actionkey): self
    {
        return clone($this, ['actionkey' => $actionkey]);
    }

    /**
     * Serialisiert die Options in ein Billomat-Payload-Array.
     *
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'comment' => $this->comment,
            'actionkey' => $this->actionkey?->value,
        ], static fn (mixed $v): bool => null !== $v);
    }
}
