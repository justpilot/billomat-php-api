<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Generator;
use Justpilot\Billomat\Exception\AuthenticationException;
use Justpilot\Billomat\Exception\HttpException;
use Justpilot\Billomat\Exception\ValidationException;
use Justpilot\Billomat\Model\Confirmation;
use Justpilot\Billomat\Model\ConfirmationComment;
use Justpilot\Billomat\Model\ConfirmationItem;
use Justpilot\Billomat\Model\ConfirmationPdf;
use Justpilot\Billomat\Model\ConfirmationTag;
use Justpilot\Billomat\Model\ConfirmationTagCloudEntry;
use Justpilot\Billomat\Model\Enum\ConfirmationCommentActionKey;
use Justpilot\Billomat\Model\Enum\InvoicePdfType;
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
 *  - PUT    /confirmations/{id}/clear   (als erledigt markieren)
 *  - PUT    /confirmations/{id}/undo    (Status zurücksetzen)
 *  - POST   /confirmations/{id}/email
 *  - GET    /confirmations/{id}/pdf
 *  - PUT    /confirmations/{id}/upload-signature
 */
final class ConfirmationsApi extends AbstractApi
{
    /**
     * Positionen, siehe {@see ItemsApi}.
     *
     * @var ItemsApi<ConfirmationItem, ConfirmationItemCreateOptions>
     */
    public private(set) ItemsApi $items {
        get => $this->items ??= new ItemsApi($this->http, 'confirmation', ConfirmationItem::fromArray(...), ConfirmationItemCreateOptions::class);
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
     * @param array<string, scalar|array|null> $filters
     *
     * @return list<Confirmation>
     */
    #[NoDiscard]
    public function list(array $filters = []): array
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
     * @param array<string, scalar|array|null> $filters
     *
     * @return Page<Confirmation>
     */
    #[NoDiscard]
    public function listPage(array $filters = []): Page
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
     * @param array<string, scalar|array|null> $filters
     *
     * @return Generator<int, Confirmation>
     */
    #[NoDiscard]
    public function iterateAll(array $filters = [], int $pageSize = 100): Generator
    {
        yield from $this->iterateResource('/confirmations', 'confirmations', 'confirmation', Confirmation::fromArray(...), $filters, $pageSize);
    }

    #[NoDiscard]
    public function get(int $id): ?Confirmation
    {
        $data = $this->getJsonOrNull("/confirmations/{$id}");

        if (null === $data) {
            return null;
        }

        $row = $data['confirmation'] ?? null;

        if (!\is_array($row)) {
            return null;
        }

        return Confirmation::fromArray($row);
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

    public function cancel(int $id): void
    {
        $this->putVoid("/confirmations/{$id}/cancel");
    }

    /** Markiert eine Auftragsbestätigung als erledigt (Status → CLEARED). */
    public function clear(int $id): void
    {
        $this->putVoid("/confirmations/{$id}/clear");
    }

    /** Setzt einen Status zurück auf OPEN (Rückgängigmachen von clear/cancel). */
    public function undo(int $id): void
    {
        $this->putVoid("/confirmations/{$id}/undo");
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
     * Ruft das PDF einer Auftragsbestätigung ab.
     *
     * @return ConfirmationPdf|string PDF-Model im JSON-Modus, binär im Raw-Modus
     */
    #[NoDiscard]
    public function pdf(int $id, ?InvoicePdfType $type = null, bool $rawPdf = false): ConfirmationPdf|string
    {
        $query = [];

        if ($type instanceof InvoicePdfType) {
            $query['type'] = $type->value;
        }

        if ($rawPdf) {
            $query['format'] = 'pdf';

            return $this->getRaw("/confirmations/{$id}/pdf", $query);
        }

        $data = $this->getJson("/confirmations/{$id}/pdf", $query);

        return ConfirmationPdf::fromArray($this->unwrapEnvelope($data, 'pdf', 'fetching confirmation PDF'));
    }
}
