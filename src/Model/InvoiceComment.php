<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use DateTimeImmutable;
use Justpilot\Billomat\Model\Enum\InvoiceCommentActionKey;
use Throwable;

use const DATE_ATOM;

/**
 * Repräsentiert einen Kommentar an einer Rechnung.
 *
 * Dokumentation: https://www.billomat.com/api/rechnungen/kommentare/
 *
 * Hinweis zum `actionkey`:
 *  - Bei System-Kommentaren (Statuswechsel, Versand, Zahlung) ist das Feld gesetzt.
 *  - Bei manuell angelegten Kommentaren ist das Feld typischerweise leer.
 *  - Unbekannte Werte werden über das Enum als null geparst; `actionkeyRaw`
 *    enthält den ursprünglichen String aus der API.
 */
final readonly class InvoiceComment
{
    public function __construct(
        /** Interne ID des Kommentars. */
        public ?int $id,
        /** ID der zugehörigen Rechnung. */
        public int $invoiceId,
        /** Kommentartext. */
        public ?string $comment = null,
        /** Erstellungszeitpunkt. */
        public ?DateTimeImmutable $created = null,
        /** ID des Users, der den Kommentar erzeugt hat (oder null bei System). */
        public ?int $userId = null,
        /** ActionKey als Enum, sofern bekannt. */
        public ?InvoiceCommentActionKey $actionkey = null,
        /** Roher ActionKey-String aus der API (auch wenn das Enum den Wert nicht kennt). */
        public ?string $actionkeyRaw = null,
    ) {
    }

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $actionkeyRaw = isset($data['actionkey']) && '' !== $data['actionkey']
            ? (string) $data['actionkey']
            : null;

        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            invoiceId: (int) ($data['invoice_id'] ?? 0),
            comment: isset($data['comment']) ? (string) $data['comment'] : null,
            created: self::parseDateTime($data['created'] ?? null),
            userId: isset($data['user_id']) && '' !== $data['user_id']
                ? (int) $data['user_id']
                : null,
            actionkey: null !== $actionkeyRaw
                ? InvoiceCommentActionKey::tryFrom($actionkeyRaw)
                : null,
            actionkeyRaw: $actionkeyRaw,
        );
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'created' => $this->created?->format(DATE_ATOM),
            'invoice_id' => $this->invoiceId,
            'user_id' => $this->userId,
            'comment' => $this->comment,
            'actionkey' => $this->actionkeyRaw,
        ];
    }

    private static function parseDateTime(mixed $value): ?DateTimeImmutable
    {
        if (!\is_string($value) || '' === trim($value)) {
            return null;
        }

        try {
            return new DateTimeImmutable($value);
        } catch (Throwable) {
            return null;
        }
    }
}
