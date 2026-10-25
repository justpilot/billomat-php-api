<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\Enum\InvoiceCommentActionKey;

/**
 * Typisierter Payload für POST /invoice-comments gemäß Billomat-API.
 *
 * Null-Werte bedeuten "nicht gesetzt" und werden beim Serialisieren ignoriert.
 *
 * Doku:
 * https://www.billomat.com/api/rechnungen/kommentare/
 *
 * Generiert aus tools/codegen/definitions/invoice-comments.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final readonly class InvoiceCommentCreateOptions implements WriteOptions
{
    public function __construct(
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
        public ?InvoiceCommentActionKey $actionkey = null,
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

    /** Kopie mit geändertem `comment`. */
    public function withComment(string $comment): self
    {
        return clone($this, ['comment' => $comment]);
    }

    /** Kopie mit geändertem `actionkey`. */
    public function withActionkey(?InvoiceCommentActionKey $actionkey): self
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
            'comment' => $this->comment,
            'actionkey' => $this->actionkey?->value,
            'public' => $this->public,
        ], static fn (mixed $v): bool => null !== $v);
    }
}
