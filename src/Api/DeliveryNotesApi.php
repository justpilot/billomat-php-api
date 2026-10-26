<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Generator;
use Justpilot\Billomat\Exception\AuthenticationException;
use Justpilot\Billomat\Exception\HttpException;
use Justpilot\Billomat\Exception\NotFoundException;
use Justpilot\Billomat\Exception\UnexpectedResponseException;
use Justpilot\Billomat\Exception\ValidationException;
use Justpilot\Billomat\Model\DeliveryNote;
use Justpilot\Billomat\Model\DeliveryNoteComment;
use Justpilot\Billomat\Model\DeliveryNoteItem;
use Justpilot\Billomat\Model\DeliveryNotePdf;
use Justpilot\Billomat\Model\DeliveryNoteTag;
use Justpilot\Billomat\Model\DeliveryNoteTagCloudEntry;
use Justpilot\Billomat\Model\Enum\DeliveryNoteCommentActionKey;
use Justpilot\Billomat\Model\Enum\PdfType;
use Justpilot\Billomat\Pagination\Page;
use NoDiscard;

/**
 * API-Wrapper für die Billomat-Delivery-Notes-Ressource (Lieferscheine).
 *
 * Doku: https://www.billomat.com/en/api/delivery-notes/
 *
 * Endpoints:
 *  - GET    /delivery-notes
 *  - GET    /delivery-notes/{id}
 *  - POST   /delivery-notes
 *  - PUT    /delivery-notes/{id}
 *  - DELETE /delivery-notes/{id}
 *  - PUT    /delivery-notes/{id}/complete
 *  - POST   /delivery-notes/{id}/email
 *  - GET    /delivery-notes/{id}/pdf
 *  - PUT    /delivery-notes/{id}/upload-signature
 */
final class DeliveryNotesApi extends AbstractApi
{
    /**
     * Positionen, siehe {@see ItemsApi}.
     *
     * @var ItemsApi<DeliveryNoteItem, DeliveryNoteItemCreateOptions, DeliveryNoteItemUpdateOptions>
     */
    public private(set) ItemsApi $items {
        get => $this->items ??= new ItemsApi($this->http, 'delivery-note', DeliveryNoteItem::fromArray(...), DeliveryNoteItemCreateOptions::class, DeliveryNoteItemUpdateOptions::class);
    }

    /**
     * Kommentare, siehe {@see CommentsApi}.
     *
     * @var CommentsApi<DeliveryNoteComment, DeliveryNoteCommentCreateOptions, DeliveryNoteCommentActionKey>
     */
    public private(set) CommentsApi $comments {
        get => $this->comments ??= new CommentsApi($this->http, 'delivery-note', DeliveryNoteComment::fromArray(...), DeliveryNoteCommentCreateOptions::class, DeliveryNoteCommentActionKey::class);
    }

    /**
     * Schlagworte, siehe {@see TagsApi}.
     *
     * @var TagsApi<DeliveryNoteTag, DeliveryNoteTagCloudEntry, DeliveryNoteTagCreateOptions>
     */
    public private(set) TagsApi $tags {
        get => $this->tags ??= new TagsApi($this->http, 'delivery-note', DeliveryNoteTag::fromArray(...), DeliveryNoteTagCloudEntry::fromArray(...), DeliveryNoteTagCreateOptions::class);
    }

    /**
     * @param DeliveryNoteFilter|array<string, scalar|array<scalar|null>|null> $filters
     *
     * @return list<DeliveryNote>
     */
    #[NoDiscard]
    public function list(DeliveryNoteFilter|array $filters = []): array
    {
        return $this->listResource('/delivery-notes', 'delivery-notes', 'delivery-note', DeliveryNote::fromArray(...), $filters);
    }

    /**
     * Liefert eine einzelne Seite samt Pagination-Metadaten.
     *
     * Identisch zu {@see list()}, gibt aber zusätzlich `@page`/`@per_page`/
     * `@total` aus dem Response-Envelope als {@see PageInfo} zurück. Nützlich
     * für UI mit klassischer "Seite 1/12, 234 Treffer"-Anzeige.
     *
     * @param DeliveryNoteFilter|array<string, scalar|array<scalar|null>|null> $filters
     *
     * @return Page<DeliveryNote>
     */
    #[NoDiscard]
    public function listPage(DeliveryNoteFilter|array $filters = []): Page
    {
        return $this->listResourcePage('/delivery-notes', 'delivery-notes', 'delivery-note', DeliveryNote::fromArray(...), $filters);
    }

    /**
     * Iteriert lazy durch alle Lieferscheine und yieldet sie einzeln.
     *
     * Holt seitenweise pro {@code $pageSize}-Items und stoppt automatisch,
     * sobald die letzte Seite erreicht ist (analog `auto_paging_iter()` im
     * Stripe-SDK). Filter werden bei jeder Page-Anfrage mitgesendet; `page`
     * und `per_page` darin werden überschrieben.
     *
     * @param DeliveryNoteFilter|array<string, scalar|array<scalar|null>|null> $filters
     *
     * @return Generator<int, DeliveryNote>
     */
    #[NoDiscard]
    public function iterateAll(DeliveryNoteFilter|array $filters = [], int $pageSize = 100): Generator
    {
        yield from $this->iterateResource('/delivery-notes', 'delivery-notes', 'delivery-note', DeliveryNote::fromArray(...), $filters, $pageSize);
    }

    #[NoDiscard]
    public function get(int $id): ?DeliveryNote
    {
        return $this->getResource("/delivery-notes/{$id}", 'delivery-note', DeliveryNote::fromArray(...), 'fetching delivery note');
    }

    /**
     * Wie {@see get()}, wirft aber eine {@see NotFoundException} statt null zurückzugeben.
     *
     * @throws NotFoundException wenn es den Datensatz nicht gibt (404)
     */
    #[NoDiscard]
    public function getOrFail(int $id): DeliveryNote
    {
        return $this->get($id) ?? throw $this->notFound("/delivery-notes/{$id}");
    }

    /**
     * @throws ValidationException
     * @throws AuthenticationException
     * @throws HttpException
     */
    public function create(DeliveryNoteCreateOptions $options): DeliveryNote
    {
        $payload = ['delivery-note' => $options->toArray()];

        $data = $this->postJson('/delivery-notes', $payload);

        return DeliveryNote::fromArray($this->unwrapEnvelope($data, 'delivery-note', 'creating delivery note'));
    }

    public function update(int $id, DeliveryNoteUpdateOptions $options): DeliveryNote
    {
        $payload = ['delivery-note' => $options->toArray()];

        $data = $this->putJson("/delivery-notes/{$id}", $payload);

        return DeliveryNote::fromArray($this->unwrapEnvelope($data, 'delivery-note', 'updating delivery note'));
    }

    public function complete(int $id, ?int $templateId = null): void
    {
        $body = [];

        if (null !== $templateId) {
            $body['template_id'] = $templateId;
        }

        $payload = ['delivery-note' => $body];

        $this->putVoid("/delivery-notes/{$id}/complete", $payload);
    }

    public function delete(int $id): void
    {
        $this->deleteVoid("/delivery-notes/{$id}");
    }

    public function email(int $id, ?EmailOptions $options = null): void
    {
        $payload = ['email' => $options?->toArray() ?? []];

        $this->postJson("/delivery-notes/{$id}/email", $payload);
    }

    public function uploadSignature(int $id, string $base64Pdf): void
    {
        $payload = ['upload' => ['base64file' => $base64Pdf]];

        $this->putVoid("/delivery-notes/{$id}/upload-signature", $payload);
    }

    /**
     * Ruft das PDF eines Lieferscheins als JSON ab (Metadaten und base64-kodierter Inhalt).
     *
     * Entspricht GET /delivery-notes/{id}/pdf. Optionaler Typ: `PdfType::SIGNED` (type=signed)
     * oder `PdfType::PRINT` (type=print). Die rohen Bytes liefert {@see pdfContent()}.
     */
    #[NoDiscard]
    public function pdf(int $id, ?PdfType $type = null): DeliveryNotePdf
    {
        $data = $this->getJson("/delivery-notes/{$id}/pdf", self::pdfQuery($type));

        return DeliveryNotePdf::fromArray($this->unwrapEnvelope($data, 'pdf', 'fetching delivery note PDF'));
    }

    /**
     * Ruft das PDF eines Lieferscheins als binären Inhalt ab (GET /delivery-notes/{id}/pdf?format=pdf).
     *
     * @throws UnexpectedResponseException wenn Billomat kein PDF liefert
     */
    #[NoDiscard]
    public function pdfContent(int $id, ?PdfType $type = null): string
    {
        return $this->getPdf("/delivery-notes/{$id}/pdf", [...self::pdfQuery($type), 'format' => 'pdf']);
    }
}
