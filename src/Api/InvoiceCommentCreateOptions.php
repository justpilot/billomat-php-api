<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\Enum\InvoiceCommentActionKey;

/**
 * Typisierter Payload für POST /invoice-comments.
 *
 * Doku: https://www.billomat.com/api/rechnungen/kommentare/
 */
final class InvoiceCommentCreateOptions
{
    /**
     * Optionaler ActionKey. Für manuell angelegte Kommentare normalerweise nicht
     * relevant – Billomat setzt den Wert bei system-getriebenen Kommentaren selbst.
     */
    public ?InvoiceCommentActionKey $actionkey = null;

    /**
     * Sichtbarkeit für die Aktivitäten-Liste des Kunden. Default laut Billomat: false.
     */
    public ?bool $public = null;

    public function __construct(
        /** ID der Rechnung (Pflichtfeld). */
        public int $invoiceId,
        /** Kommentartext (Pflichtfeld). */
        public string $comment,
    ) {
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        $data = [
            'invoice_id' => $this->invoiceId,
            'comment' => $this->comment,
            'actionkey' => $this->actionkey?->value,
            'public' => $this->public,
        ];

        return array_filter($data, static fn (mixed $v): bool => null !== $v);
    }
}
