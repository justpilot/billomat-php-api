<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use DateTimeImmutable;
use Justpilot\Billomat\Internal\ScalarCaster;
use Justpilot\Billomat\Model\Enum\DeliveryNoteCommentActionKey;

use const DATE_ATOM;

/**
 * Kommentar an einem Lieferschein.
 *
 * Dieses Model bildet die Struktur von GET /delivery-note-comments?delivery_note_id={id} bzw. GET /delivery-note-comments/{id} ab.
 *
 * Hinweis zum `actionkey`:
 *  - Bei System-Kommentaren (Statuswechsel, Versand, Zahlung) ist das Feld gesetzt.
 *  - Bei manuell angelegten Kommentaren ist das Feld typischerweise leer.
 *  - Unbekannte Werte werden über das Enum als null geparst; `actionkeyRaw`
 *    enthält den ursprünglichen String aus der API.
 *
 * Dokumentation:
 * https://www.billomat.com/en/api/delivery-notes/comments/
 *
 * Generiert aus tools/codegen/definitions/delivery-note-comments.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final readonly class DeliveryNoteComment
{
    /** Billomat-Felder, die das Model abbildet. */
    private const array FIELDS = [
        'id' => true,
        'delivery_note_id' => true,
        'comment' => true,
        'created' => true,
        'user_id' => true,
        'actionkey' => true,
        'customfield' => true,
        'public' => true,
        'by_client' => true,
        'email_id' => true,
        'client_id' => true,
    ];

    /** @param array<string, mixed> $extra */
    public function __construct(
        /** Interne ID des Kommentars. */
        public ?int $id,
        /** ID des Dokuments, an dem der Kommentar hängt. */
        public int $deliveryNoteId,
        /** Kommentartext. */
        public ?string $comment = null,
        /** Erstellungszeitpunkt. */
        public ?DateTimeImmutable $created = null,
        /** ID des Users, der den Kommentar erzeugt hat (oder null bei System). */
        public ?int $userId = null,
        /**
         * ActionKey als Enum, sofern bekannt.
         *
         * Beim Anlegen normalerweise nicht relevant – Billomat setzt den Wert
         * bei system-getriebenen Kommentaren selbst.
         */
        public ?DeliveryNoteCommentActionKey $actionkey = null,
        /** Roher ActionKey-String aus der API (auch wenn das Enum den Wert nicht kennt). */
        public ?string $actionkeyRaw = null,
        /** Freies Zusatzfeld (siehe Custom-Meta-Attribute). */
        public ?string $customfield = null,
        /** Sichtbarkeit für die Aktivitäten-Liste des Kunden. */
        public ?bool $public = null,
        /** Hat der Kunde den Kommentar verfasst (Kundenportal)? */
        public ?bool $byClient = null,
        /** ID der zugehörigen E-Mail, falls der Kommentar aus einem Versand stammt. */
        public ?int $emailId = null,
        /** ID des Kunden. */
        public ?int $clientId = null,
        /**
         * Felder, die Billomat liefert, das SDK aber (noch) nicht abbildet – roh, wie in der Response.
         *
         * Neue Billomat-Felder gehen so nicht verloren; toArray() gibt sie mit aus.
         */
        public array $extra = [],
    ) {
    }

    /**
     * Hydriert das Model aus einem Billomat-Response-Array.
     *
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: ScalarCaster::toIntOrNull($data['id'] ?? null),
            deliveryNoteId: ScalarCaster::toIntOrNull($data['delivery_note_id'] ?? null) ?? 0,
            comment: ScalarCaster::toStringOrNull($data['comment'] ?? null),
            created: ScalarCaster::toDateTimeOrNull($data['created'] ?? null),
            userId: ScalarCaster::toIntOrNull($data['user_id'] ?? null),
            actionkey: DeliveryNoteCommentActionKey::tryFrom(ScalarCaster::toStringOrNull($data['actionkey'] ?? null) ?? ''),
            actionkeyRaw: ScalarCaster::toStringOrNull($data['actionkey'] ?? null),
            customfield: ScalarCaster::toStringOrNull($data['customfield'] ?? null),
            public: ScalarCaster::toBoolOrNull($data['public'] ?? null),
            byClient: ScalarCaster::toBoolOrNull($data['by_client'] ?? null),
            emailId: ScalarCaster::toIntOrNull($data['email_id'] ?? null),
            clientId: ScalarCaster::toIntOrNull($data['client_id'] ?? null),
            extra: array_diff_key($data, self::FIELDS),
        );
    }

    /**
     * Exportiert das Model als Array mit Billomat-Feldnamen.
     *
     * Praktisch für Debugging oder Log-Ausgaben.
     *
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            ...$this->extra,
            'id' => $this->id,
            'created' => $this->created?->format(DATE_ATOM),
            'delivery_note_id' => $this->deliveryNoteId,
            'user_id' => $this->userId,
            'comment' => $this->comment,
            'actionkey' => $this->actionkeyRaw,
            'customfield' => $this->customfield,
            'public' => $this->public,
            'by_client' => $this->byClient,
            'email_id' => $this->emailId,
            'client_id' => $this->clientId,
        ];
    }
}
