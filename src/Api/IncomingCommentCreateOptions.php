<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\Enum\IncomingCommentActionKey;

/**
 * Typisierter Payload für POST /incoming-comments gemäß Billomat-API.
 *
 * Null-Werte bedeuten "nicht gesetzt" und werden beim Serialisieren ignoriert.
 *
 * Doku:
 * https://www.billomat.com/api/eingangsrechnungen/kommentare/
 *
 * Generiert aus tools/codegen/definitions/incoming-comments.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final class IncomingCommentCreateOptions
{
    public function __construct(
        /**
         * ID des Dokuments, an dem der Kommentar hängt.
         *
         * Billomat-Feld: incoming_id
         * Typ: INT
         */
        public int $incomingId,
        /**
         * Kommentartext.
         *
         * Billomat-Feld: comment
         * Typ: ALNUM
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
    public ?IncomingCommentActionKey $actionkey = null;

    /**
     * Serialisiert die Options in ein Billomat-Payload-Array.
     *
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        $data = [
            'incoming_id' => $this->incomingId,
            'comment' => $this->comment,
            'actionkey' => $this->actionkey?->value,
        ];

        // Null-Felder entfernen
        return array_filter($data, static fn (mixed $v): bool => null !== $v);
    }
}
