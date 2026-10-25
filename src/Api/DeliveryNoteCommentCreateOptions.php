<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\Enum\DeliveryNoteCommentActionKey;

/**
 * Typisierter Payload für POST /delivery-note-comments gemäß Billomat-API.
 *
 * Null-Werte bedeuten "nicht gesetzt" und werden beim Serialisieren ignoriert.
 *
 * Doku:
 * https://www.billomat.com/en/api/delivery-notes/comments/
 *
 * Generiert aus tools/codegen/definitions/delivery-note-comments.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final class DeliveryNoteCommentCreateOptions implements WriteOptions
{
    public function __construct(
        /**
         * ID des Dokuments, an dem der Kommentar hängt.
         *
         * Billomat-Feld: delivery_note_id
         */
        public int $deliveryNoteId,
        /**
         * Kommentartext.
         *
         * Billomat-Feld: comment
         */
        public string $comment,
    ) {
    }

    /**
     * ActionKey als Enum, sofern bekannt.
     *
     * Beim Anlegen normalerweise nicht relevant – Billomat setzt den Wert
     * bei system-getriebenen Kommentaren selbst.
     *
     * Billomat-Feld: actionkey
     */
    public ?DeliveryNoteCommentActionKey $actionkey = null;

    /**
     * Serialisiert die Options in ein Billomat-Payload-Array.
     *
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        $data = [
            'delivery_note_id' => $this->deliveryNoteId,
            'comment' => $this->comment,
            'actionkey' => $this->actionkey?->value,
        ];

        // Null-Felder entfernen
        return array_filter($data, static fn (mixed $v): bool => null !== $v);
    }
}
