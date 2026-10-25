<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Generator;
use Justpilot\Billomat\Exception\AuthenticationException;
use Justpilot\Billomat\Exception\HttpException;
use Justpilot\Billomat\Exception\ValidationException;
use Justpilot\Billomat\Model\CreditNote;
use Justpilot\Billomat\Model\CreditNoteComment;
use Justpilot\Billomat\Model\CreditNoteItem;
use Justpilot\Billomat\Model\CreditNotePayment;
use Justpilot\Billomat\Model\CreditNotePdf;
use Justpilot\Billomat\Model\CreditNoteTag;
use Justpilot\Billomat\Model\CreditNoteTagCloudEntry;
use Justpilot\Billomat\Model\Enum\CreditNoteCommentActionKey;
use Justpilot\Billomat\Model\Enum\PdfType;
use Justpilot\Billomat\Pagination\Page;
use NoDiscard;

/**
 * API-Wrapper für die Billomat-Credit-Notes-Ressource (Gutschriften).
 *
 * Doku: https://www.billomat.com/en/api/credit-notes/
 *
 * Endpoints:
 *  - GET    /credit-notes
 *  - GET    /credit-notes/{id}
 *  - POST   /credit-notes
 *  - PUT    /credit-notes/{id}
 *  - DELETE /credit-notes/{id}
 *  - PUT    /credit-notes/{id}/complete
 *  - PUT    /credit-notes/{id}/cancel
 *  - PUT    /credit-notes/{id}/uncancel
 *  - POST   /credit-notes/{id}/email
 *  - GET    /credit-notes/{id}/pdf
 *  - PUT    /credit-notes/{id}/upload-signature
 */
final class CreditNotesApi extends AbstractApi
{
    /**
     * Positionen, siehe {@see ItemsApi}.
     *
     * @var ItemsApi<CreditNoteItem, CreditNoteItemCreateOptions>
     */
    public private(set) ItemsApi $items {
        get => $this->items ??= new ItemsApi($this->http, 'credit-note', CreditNoteItem::fromArray(...), CreditNoteItemCreateOptions::class);
    }

    /**
     * Zahlungen, siehe {@see PaymentsApi}.
     *
     * @var PaymentsApi<CreditNotePayment, CreditNotePaymentCreateOptions>
     */
    public private(set) PaymentsApi $payments {
        get => $this->payments ??= new PaymentsApi($this->http, 'credit-note', CreditNotePayment::fromArray(...), CreditNotePaymentCreateOptions::class);
    }

    /**
     * Kommentare, siehe {@see CommentsApi}.
     *
     * @var CommentsApi<CreditNoteComment, CreditNoteCommentCreateOptions, CreditNoteCommentActionKey>
     */
    public private(set) CommentsApi $comments {
        get => $this->comments ??= new CommentsApi($this->http, 'credit-note', CreditNoteComment::fromArray(...), CreditNoteCommentCreateOptions::class, CreditNoteCommentActionKey::class);
    }

    /**
     * Schlagworte, siehe {@see TagsApi}.
     *
     * @var TagsApi<CreditNoteTag, CreditNoteTagCloudEntry, CreditNoteTagCreateOptions>
     */
    public private(set) TagsApi $tags {
        get => $this->tags ??= new TagsApi($this->http, 'credit-note', CreditNoteTag::fromArray(...), CreditNoteTagCloudEntry::fromArray(...), CreditNoteTagCreateOptions::class);
    }

    /**
     * @param CreditNoteFilter|array<string, scalar|array|null> $filters
     *
     * @return list<CreditNote>
     */
    #[NoDiscard]
    public function list(CreditNoteFilter|array $filters = []): array
    {
        return $this->listResource('/credit-notes', 'credit-notes', 'credit-note', CreditNote::fromArray(...), $filters);
    }

    /**
     * Liefert eine einzelne Seite samt Pagination-Metadaten.
     *
     * Identisch zu {@see list()}, gibt aber zusätzlich `@page`/`@per_page`/
     * `@total` aus dem Response-Envelope als {@see PageInfo} zurück. Nützlich
     * für UI mit klassischer "Seite 1/12, 234 Treffer"-Anzeige.
     *
     * @param CreditNoteFilter|array<string, scalar|array|null> $filters
     *
     * @return Page<CreditNote>
     */
    #[NoDiscard]
    public function listPage(CreditNoteFilter|array $filters = []): Page
    {
        return $this->listResourcePage('/credit-notes', 'credit-notes', 'credit-note', CreditNote::fromArray(...), $filters);
    }

    /**
     * Iteriert lazy durch alle Gutschriften und yieldet sie einzeln.
     *
     * Holt seitenweise pro {@code $pageSize}-Items und stoppt automatisch,
     * sobald die letzte Seite erreicht ist (analog `auto_paging_iter()` im
     * Stripe-SDK). Filter werden bei jeder Page-Anfrage mitgesendet; `page`
     * und `per_page` darin werden überschrieben.
     *
     * @param CreditNoteFilter|array<string, scalar|array|null> $filters
     *
     * @return Generator<int, CreditNote>
     */
    #[NoDiscard]
    public function iterateAll(CreditNoteFilter|array $filters = [], int $pageSize = 100): Generator
    {
        yield from $this->iterateResource('/credit-notes', 'credit-notes', 'credit-note', CreditNote::fromArray(...), $filters, $pageSize);
    }

    #[NoDiscard]
    public function get(int $id): ?CreditNote
    {
        $data = $this->getJsonOrNull("/credit-notes/{$id}");

        if (null === $data) {
            return null;
        }

        $row = $data['credit-note'] ?? null;

        if (!\is_array($row)) {
            return null;
        }

        return CreditNote::fromArray($row);
    }

    /**
     * @throws ValidationException
     * @throws AuthenticationException
     * @throws HttpException
     */
    public function create(CreditNoteCreateOptions $options): CreditNote
    {
        $payload = ['credit-note' => $options->toArray()];

        $data = $this->postJson('/credit-notes', $payload);

        return CreditNote::fromArray($this->unwrapEnvelope($data, 'credit-note', 'creating credit note'));
    }

    public function update(int $id, CreditNoteUpdateOptions $options): CreditNote
    {
        $payload = ['credit-note' => $options->toArray()];

        $data = $this->putJson("/credit-notes/{$id}", $payload);

        return CreditNote::fromArray($this->unwrapEnvelope($data, 'credit-note', 'updating credit note'));
    }

    public function complete(int $id, ?int $templateId = null): void
    {
        $body = [];

        if (null !== $templateId) {
            $body['template_id'] = $templateId;
        }

        $payload = ['credit-note' => $body];

        $this->putVoid("/credit-notes/{$id}/complete", $payload);
    }

    public function delete(int $id): void
    {
        $this->deleteVoid("/credit-notes/{$id}");
    }

    public function cancel(int $id): void
    {
        $this->putVoid("/credit-notes/{$id}/cancel");
    }

    public function uncancel(int $id): void
    {
        $this->putVoid("/credit-notes/{$id}/uncancel");
    }

    public function email(int $id, ?EmailOptions $options = null): void
    {
        $payload = ['email' => $options?->toArray() ?? []];

        $this->postJson("/credit-notes/{$id}/email", $payload);
    }

    public function uploadSignature(int $id, string $base64Pdf): void
    {
        $payload = ['upload' => ['base64file' => $base64Pdf]];

        $this->putVoid("/credit-notes/{$id}/upload-signature", $payload);
    }

    /**
     * @return CreditNotePdf|string PDF-Model im JSON-Modus, binär im Raw-Modus
     */
    #[NoDiscard]
    public function pdf(int $id, ?PdfType $type = null, bool $rawPdf = false): CreditNotePdf|string
    {
        $query = [];

        if ($type instanceof PdfType) {
            $query['type'] = $type->value;
        }

        if ($rawPdf) {
            $query['format'] = 'pdf';

            return $this->getPdf("/credit-notes/{$id}/pdf", $query);
        }

        $data = $this->getJson("/credit-notes/{$id}/pdf", $query);

        return CreditNotePdf::fromArray($this->unwrapEnvelope($data, 'pdf', 'fetching credit note PDF'));
    }
}
