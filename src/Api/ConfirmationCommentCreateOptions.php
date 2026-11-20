<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\Enum\ConfirmationCommentActionKey;

/**
 * Typisierter Payload für POST /confirmation-comments gemäß Billomat-API.
 *
 * Null-Werte bedeuten "nicht gesetzt" und werden beim Serialisieren ignoriert.
 *
 * Doku:
 * https://www.billomat.com/api/auftragsbestaetigungen/
 *
 * Generiert aus tools/codegen/definitions/confirmation-comments.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final readonly class ConfirmationCommentCreateOptions implements WriteOptions
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
        public ?ConfirmationCommentActionKey $actionKey = null,
    ) {
    }

    /** Kopie mit geändertem `comment`. */
    public function withComment(string $comment): self
    {
        return clone($this, ['comment' => $comment]);
    }

    /** Kopie mit geändertem `actionKey`. */
    public function withActionKey(?ConfirmationCommentActionKey $actionKey): self
    {
        return clone($this, ['actionKey' => $actionKey]);
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
            'actionkey' => $this->actionKey?->value,
        ], static fn (mixed $v): bool => null !== $v);
    }
}
