<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Generator;
use Justpilot\Billomat\Exception\AuthenticationException;
use Justpilot\Billomat\Exception\HttpException;
use Justpilot\Billomat\Exception\NotFoundException;
use Justpilot\Billomat\Exception\UnexpectedResponseException;
use Justpilot\Billomat\Exception\ValidationException;
use Justpilot\Billomat\Model\Confirmation;
use Justpilot\Billomat\Model\ConfirmationComment;
use Justpilot\Billomat\Model\ConfirmationItem;
use Justpilot\Billomat\Model\ConfirmationPdf;
use Justpilot\Billomat\Model\ConfirmationTag;
use Justpilot\Billomat\Model\ConfirmationTagCloudEntry;
use Justpilot\Billomat\Model\Enum\ConfirmationCommentActionKey;
use Justpilot\Billomat\Model\Enum\PdfType;
use Justpilot\Billomat\Pagination\Page;
use NoDiscard;

/**
 * API-Wrapper für die Billomat-Confirmations-Ressource (Auftragsbestätigungen).
 *
 * Doku: https://www.billomat.com/en/api/confirmations/
 *
 * Endpoints:
 *  - GET    /confirmations
 *  - GET    /confirmations/{id}
 *  - POST   /confirmations
 *  - PUT    /confirmations/{id}
 *  - DELETE /confirmations/{id}
 *  - PUT    /confirmations/{id}/complete
 *  - PUT    /confirmations/{id}/cancel
 *  - PUT    /confirmations/{id}/uncancel
 *  - PUT    /confirmations/{id}/clear   (als erledigt markieren)
 *  - PUT    /confirmations/{id}/unclear (Erledigt-Markierung zurücknehmen)
 *  - POST   /confirmations/{id}/email
 *  - GET    /confirmations/{id}/pdf
 *  - PUT    /confirmations/{id}/upload-signature
 */
final class ConfirmationsApi extends AbstractApi
{
    /**
     * Positionen, siehe {@see ItemsApi}.
     *
     * @var ItemsApi<ConfirmationItem, ConfirmationItemCreateOptions, ConfirmationItemUpdateOptions>
     */
    public private(set) ItemsApi $items {
        get => $this->items ??= new ItemsApi($this->http, 'confirmation', ConfirmationItem::fromArray(...), ConfirmationItemCreateOptions::class, ConfirmationItemUpdateOptions::class);
    }

    /**
     * Kommentare, siehe {@see CommentsApi}.
     *
     * @var CommentsApi<ConfirmationComment, ConfirmationCommentCreateOptions, ConfirmationCommentActionKey>
     */
    public private(set) CommentsApi $comments {
        get => $this->comments ??= new CommentsApi($this->http, 'confirmation', ConfirmationComment::fromArray(...), ConfirmationCommentCreateOptions::class, ConfirmationCommentActionKey::class);
    }

    /**
     * Schlagworte, siehe {@see TagsApi}.
     *
     * @var TagsApi<ConfirmationTag, ConfirmationTagCloudEntry, ConfirmationTagCreateOptions>
     */
    public private(set) TagsApi $tags {
        get => $this->tags ??= new TagsApi($this->http, 'confirmation', ConfirmationTag::fromArray(...), ConfirmationTagCloudEntry::fromArray(...), ConfirmationTagCreateOptions::class);
    }

    /**
     * @param ConfirmationFilter|array<string, scalar|array<scalar|null>|null> $filters
     *
     * @return list<Confirmation>
     */
    #[NoDiscard]
    public function list(ConfirmationFilter|array $filters = []): array
    {
        return $this->listResource('/confirmations', 'confirmations', 'confirmation', Confirmation::fromArray(...), $filters);
    }

    /**
     * Liefert eine einzelne Seite samt Pagination-Metadaten.
     *
     * Identisch zu {@see list()}, gibt aber zusätzlich `@page`/`@per_page`/
     * `@total` aus dem Response-Envelope als {@see PageInfo} zurück. Nützlich
     * für UI mit klassischer "Seite 1/12, 234 Treffer"-Anzeige.
     *
     * @param ConfirmationFilter|array<string, scalar|array<scalar|null>|null> $filters
     *
     * @return Page<Confirmation>
     */
    #[NoDiscard]
    public function listPage(ConfirmationFilter|array $filters = []): Page
    {
        return $this->listResourcePage('/confirmations', 'confirmations', 'confirmation', Confirmation::fromArray(...), $filters);
    }

    /**
     * Iteriert lazy durch alle Auftragsbestätigungen und yieldet sie einzeln.
     *
     * Holt seitenweise pro {@code $pageSize}-Items und stoppt automatisch,
     * sobald die letzte Seite erreicht ist (analog `auto_paging_iter()` im
     * Stripe-SDK). Filter werden bei jeder Page-Anfrage mitgesendet; `page`
     * und `per_page` darin werden überschrieben.
     *
     * @param ConfirmationFilter|array<string, scalar|array<scalar|null>|null> $filters
     *
     * @return Generator<int, Confirmation>
     */
    #[NoDiscard]
    public function iterateAll(ConfirmationFilter|array $filters = [], int $pageSize = 100): Generator
    {
        yield from $this->iterateResource('/confirmations', 'confirmations', 'confirmation', Confirmation::fromArray(...), $filters, $pageSize);
    }

    #[NoDiscard]
    public function get(int $id): ?Confirmation
    {
        return $this->getResource("/confirmations/{$id}", 'confirmation', Confirmation::fromArray(...), 'fetching confirmation');
    }

    /**
     * Wie {@see get()}, wirft aber eine {@see NotFoundException} statt null zurückzugeben.
     *
     * @throws NotFoundException wenn es den Datensatz nicht gibt (404)
     */
    #[NoDiscard]
    public function getOrFail(int $id): Confirmation
    {
        return $this->get($id) ?? throw $this->notFound("/confirmations/{$id}");
    }

    /**
     * @throws ValidationException
     * @throws AuthenticationException
     * @throws HttpException
     */
    public function create(ConfirmationCreateOptions $options): Confirmation
    {
        $payload = ['confirmation' => $options->toArray()];

        $data = $this->postJson('/confirmations', $payload);

        return Confirmation::fromArray($this->unwrapEnvelope($data, 'confirmation', 'creating confirmation'));
    }

    public function update(int $id, ConfirmationUpdateOptions $options): Confirmation
    {
        $payload = ['confirmation' => $options->toArray()];

        $data = $this->putJson("/confirmations/{$id}", $payload);

        return Confirmation::fromArray($this->unwrapEnvelope($data, 'confirmation', 'updating confirmation'));
    }

    public function complete(int $id, ?int $templateId = null): void
    {
        $body = [];

        if (null !== $templateId) {
            $body['template_id'] = $templateId;
        }

        $payload = ['confirmation' => $body];

        $this->putVoid("/confirmations/{$id}/complete", $payload);
    }

    public function delete(int $id): void
    {
        $this->deleteVoid("/confirmations/{$id}");
    }

    /** Storniert eine Auftragsbestätigung (Status → CANCELED). */
    public function cancel(int $id): void
    {
        $this->putVoid("/confirmations/{$id}/cancel");
    }

    /** Hebt die Stornierung auf (CANCELED → COMPLETED). */
    public function uncancel(int $id): void
    {
        $this->putVoid("/confirmations/{$id}/uncancel");
    }

    /** Markiert eine Auftragsbestätigung als erledigt (Status → CLEARED). */
    public function clear(int $id): void
    {
        $this->putVoid("/confirmations/{$id}/clear");
    }

    /** Nimmt die Erledigt-Markierung zurück (CLEARED → COMPLETED). */
    public function unclear(int $id): void
    {
        $this->putVoid("/confirmations/{$id}/unclear");
    }

    public function email(int $id, ?EmailOptions $options = null): void
    {
        $payload = ['email' => $options?->toArray() ?? []];

        $this->postJson("/confirmations/{$id}/email", $payload);
    }

    public function uploadSignature(int $id, string $base64Pdf): void
    {
        $payload = ['upload' => ['base64file' => $base64Pdf]];

        $this->putVoid("/confirmations/{$id}/upload-signature", $payload);
    }

    /**
     * Ruft das PDF einer Auftragsbestätigung als JSON ab (Metadaten und base64-kodierter Inhalt).
     *
     * Entspricht GET /confirmations/{id}/pdf. Optionaler Typ: `PdfType::SIGNED` (type=signed)
     * oder `PdfType::PRINT` (type=print). Die rohen Bytes liefert {@see pdfContent()}.
     */
    #[NoDiscard]
    public function pdf(int $id, ?PdfType $type = null): ConfirmationPdf
    {
        $data = $this->getJson("/confirmations/{$id}/pdf", self::pdfQuery($type));

        return ConfirmationPdf::fromArray($this->unwrapEnvelope($data, 'pdf', 'fetching confirmation PDF'));
    }

    /**
     * Ruft das PDF einer Auftragsbestätigung als binären Inhalt ab (GET /confirmations/{id}/pdf?format=pdf).
     *
     * @throws UnexpectedResponseException wenn Billomat kein PDF liefert
     */
    #[NoDiscard]
    public function pdfContent(int $id, ?PdfType $type = null): string
    {
        return $this->getPdf("/confirmations/{$id}/pdf", [...self::pdfQuery($type), 'format' => 'pdf']);
    }
}
