<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use DateTimeImmutable;
use Justpilot\Billomat\Internal\ScalarCaster;
use Justpilot\Billomat\Model\Enum\IncomingCommentActionKey;

use const DATE_ATOM;

/**
 * Kommentar an einer Eingangsrechnung.
 *
 * Dieses Model bildet die Struktur von GET /incoming-comments?incoming_id={id} bzw. GET /incoming-comments/{id} ab.
 *
 * Hinweis zum `actionkey`:
 *  - Bei System-Kommentaren (Statuswechsel, Versand, Zahlung) ist das Feld gesetzt.
 *  - Bei manuell angelegten Kommentaren ist das Feld typischerweise leer.
 *  - Unbekannte Werte werden über das Enum als null geparst; `actionkeyRaw`
 *    enthält den ursprünglichen String aus der API.
 *
 * Dokumentation:
 * https://www.billomat.com/api/eingangsrechnungen/kommentare/
 *
 * Generiert aus tools/codegen/definitions/incoming-comments.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final readonly class IncomingComment
{
    /** Billomat-Felder, die das Model abbildet. */
    private const array FIELDS = [
        'id' => true,
        'incoming_id' => true,
        'comment' => true,
        'created' => true,
        'user_id' => true,
        'actionkey' => true,
        'customfield' => true,
    ];

    /** @param array<string, mixed> $extra */
    public function __construct(
        /** Interne ID des Kommentars. */
        public int $id,
        /** ID des Dokuments, an dem der Kommentar hängt. */
        public int $incomingId = 0,
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
        public ?IncomingCommentActionKey $actionkey = null,
        /** Roher ActionKey-String aus der API (auch wenn das Enum den Wert nicht kennt). */
        public ?string $actionkeyRaw = null,
        /** Freies Zusatzfeld (siehe Custom-Meta-Attribute). */
        public ?string $customfield = null,
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
            id: ScalarCaster::required(ScalarCaster::toIntOrNull($data['id'] ?? null), self::class, 'id'),
            incomingId: ScalarCaster::toIntOrNull($data['incoming_id'] ?? null) ?? 0,
            comment: ScalarCaster::toStringOrNull($data['comment'] ?? null),
            created: ScalarCaster::toDateTimeOrNull($data['created'] ?? null),
            userId: ScalarCaster::toIntOrNull($data['user_id'] ?? null),
            actionkey: IncomingCommentActionKey::tryFrom(ScalarCaster::toStringOrNull($data['actionkey'] ?? null) ?? ''),
            actionkeyRaw: ScalarCaster::toStringOrNull($data['actionkey'] ?? null),
            customfield: ScalarCaster::toStringOrNull($data['customfield'] ?? null),
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
            'incoming_id' => $this->incomingId,
            'user_id' => $this->userId,
            'comment' => $this->comment,
            'actionkey' => $this->actionkeyRaw,
            'customfield' => $this->customfield,
        ];
    }
}
