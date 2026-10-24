<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use DateTimeImmutable;
use Justpilot\Billomat\Internal\ScalarCaster;
use Justpilot\Billomat\Model\Enum\InvoiceCommentActionKey;

use const DATE_ATOM;

/**
 * Kommentar an einer Rechnung.
 *
 * Dieses Model bildet die Struktur von GET /invoice-comments?invoice_id={id} bzw. GET /invoice-comments/{id} ab.
 *
 * Hinweis zum `actionkey`:
 *  - Bei System-Kommentaren (Statuswechsel, Versand, Zahlung) ist das Feld gesetzt.
 *  - Bei manuell angelegten Kommentaren ist das Feld typischerweise leer.
 *  - Unbekannte Werte werden über das Enum als null geparst; `actionkeyRaw`
 *    enthält den ursprünglichen String aus der API.
 *
 * Dokumentation:
 * https://www.billomat.com/api/rechnungen/kommentare/
 *
 * Generiert aus tools/codegen/definitions/invoice-comments.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final readonly class InvoiceComment
{
    public function __construct(
        /** Interne ID des Kommentars. */
        public ?int $id,
        /** ID des Dokuments, an dem der Kommentar hängt. */
        public int $invoiceId,
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
        public ?InvoiceCommentActionKey $actionkey = null,
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
            invoiceId: ScalarCaster::toIntOrNull($data['invoice_id'] ?? null) ?? 0,
            comment: ScalarCaster::toStringOrNull($data['comment'] ?? null),
            created: ScalarCaster::toDateTimeOrNull($data['created'] ?? null),
            userId: ScalarCaster::toIntOrNull($data['user_id'] ?? null),
            actionkey: InvoiceCommentActionKey::tryFrom(ScalarCaster::toStringOrNull($data['actionkey'] ?? null) ?? ''),
            actionkeyRaw: ScalarCaster::toStringOrNull($data['actionkey'] ?? null),
            customfield: ScalarCaster::toStringOrNull($data['customfield'] ?? null),
            public: ScalarCaster::toBoolOrNull($data['public'] ?? null),
            byClient: ScalarCaster::toBoolOrNull($data['by_client'] ?? null),
            emailId: ScalarCaster::toIntOrNull($data['email_id'] ?? null),
            clientId: ScalarCaster::toIntOrNull($data['client_id'] ?? null),
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
            'id' => $this->id,
            'created' => $this->created?->format(DATE_ATOM),
            'invoice_id' => $this->invoiceId,
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
