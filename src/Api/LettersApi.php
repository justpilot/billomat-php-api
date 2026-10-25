<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Generator;
use Justpilot\Billomat\Exception\AuthenticationException;
use Justpilot\Billomat\Exception\HttpException;
use Justpilot\Billomat\Exception\ValidationException;
use Justpilot\Billomat\Model\Enum\InvoicePdfType;
use Justpilot\Billomat\Model\Enum\LetterCommentActionKey;
use Justpilot\Billomat\Model\Letter;
use Justpilot\Billomat\Model\LetterComment;
use Justpilot\Billomat\Model\LetterPdf;
use Justpilot\Billomat\Model\LetterTag;
use Justpilot\Billomat\Model\LetterTagCloudEntry;
use Justpilot\Billomat\Pagination\Page;
use NoDiscard;

/**
 * API-Wrapper für die Billomat-Letters-Ressource (Briefe).
 *
 * Doku: https://www.billomat.com/en/api/letters/
 *
 * Endpoints:
 *  - GET    /letters
 *  - GET    /letters/{id}
 *  - POST   /letters
 *  - PUT    /letters/{id}
 *  - DELETE /letters/{id}
 *  - PUT    /letters/{id}/complete
 *  - PUT    /letters/{id}/cancel
 *  - PUT    /letters/{id}/clear
 *  - PUT    /letters/{id}/undo
 *  - POST   /letters/{id}/email
 *  - GET    /letters/{id}/pdf
 *  - PUT    /letters/{id}/upload-signature
 *  - PUT    /letters/{id}/upload         (kompletten Brief-PDF hochladen)
 */
final class LettersApi extends AbstractApi
{
    /**
     * Kommentare, siehe {@see CommentsApi}.
     *
     * @var CommentsApi<LetterComment, LetterCommentCreateOptions, LetterCommentActionKey>
     */
    public private(set) CommentsApi $comments {
        get => $this->comments ??= new CommentsApi($this->http, 'letter', LetterComment::fromArray(...), LetterCommentCreateOptions::class, LetterCommentActionKey::class);
    }

    /**
     * Schlagworte, siehe {@see TagsApi}.
     *
     * @var TagsApi<LetterTag, LetterTagCloudEntry, LetterTagCreateOptions>
     */
    public private(set) TagsApi $tags {
        get => $this->tags ??= new TagsApi($this->http, 'letter', LetterTag::fromArray(...), LetterTagCloudEntry::fromArray(...), LetterTagCreateOptions::class);
    }

    /**
     * @param LetterFilter|array<string, scalar|array|null> $filters
     *
     * @return list<Letter>
     */
    #[NoDiscard]
    public function list(LetterFilter|array $filters = []): array
    {
        return $this->listResource('/letters', 'letters', 'letter', Letter::fromArray(...), $filters);
    }

    /**
     * Liefert eine einzelne Seite samt Pagination-Metadaten.
     *
     * Identisch zu {@see list()}, gibt aber zusätzlich `@page`/`@per_page`/
     * `@total` aus dem Response-Envelope als {@see PageInfo} zurück. Nützlich
     * für UI mit klassischer "Seite 1/12, 234 Treffer"-Anzeige.
     *
     * @param LetterFilter|array<string, scalar|array|null> $filters
     *
     * @return Page<Letter>
     */
    #[NoDiscard]
    public function listPage(LetterFilter|array $filters = []): Page
    {
        return $this->listResourcePage('/letters', 'letters', 'letter', Letter::fromArray(...), $filters);
    }

    /**
     * Iteriert lazy durch alle Briefe und yieldet sie einzeln.
     *
     * Holt seitenweise pro {@code $pageSize}-Items und stoppt automatisch,
     * sobald die letzte Seite erreicht ist (analog `auto_paging_iter()` im
     * Stripe-SDK). Filter werden bei jeder Page-Anfrage mitgesendet; `page`
     * und `per_page` darin werden überschrieben.
     *
     * @param LetterFilter|array<string, scalar|array|null> $filters
     *
     * @return Generator<int, Letter>
     */
    #[NoDiscard]
    public function iterateAll(LetterFilter|array $filters = [], int $pageSize = 100): Generator
    {
        yield from $this->iterateResource('/letters', 'letters', 'letter', Letter::fromArray(...), $filters, $pageSize);
    }

    #[NoDiscard]
    public function get(int $id): ?Letter
    {
        $data = $this->getJsonOrNull("/letters/{$id}");

        if (null === $data) {
            return null;
        }

        $row = $data['letter'] ?? null;

        if (!\is_array($row)) {
            return null;
        }

        return Letter::fromArray($row);
    }

    /**
     * @throws ValidationException
     * @throws AuthenticationException
     * @throws HttpException
     */
    public function create(LetterCreateOptions $options): Letter
    {
        $payload = ['letter' => $options->toArray()];

        $data = $this->postJson('/letters', $payload);

        return Letter::fromArray($this->unwrapEnvelope($data, 'letter', 'creating letter'));
    }

    public function update(int $id, LetterUpdateOptions $options): Letter
    {
        $payload = ['letter' => $options->toArray()];

        $data = $this->putJson("/letters/{$id}", $payload);

        return Letter::fromArray($this->unwrapEnvelope($data, 'letter', 'updating letter'));
    }

    public function complete(int $id, ?int $templateId = null): void
    {
        $body = [];

        if (null !== $templateId) {
            $body['template_id'] = $templateId;
        }

        $payload = ['letter' => $body];

        $this->putVoid("/letters/{$id}/complete", $payload);
    }

    public function delete(int $id): void
    {
        $this->deleteVoid("/letters/{$id}");
    }

    public function cancel(int $id): void
    {
        $this->putVoid("/letters/{$id}/cancel");
    }

    public function clear(int $id): void
    {
        $this->putVoid("/letters/{$id}/clear");
    }

    public function undo(int $id): void
    {
        $this->putVoid("/letters/{$id}/undo");
    }

    public function email(int $id, ?EmailOptions $options = null): void
    {
        $payload = ['email' => $options?->toArray() ?? []];

        $this->postJson("/letters/{$id}/email", $payload);
    }

    /**
     * Lädt das komplette Brief-PDF hoch (z.B. wenn das Layout extern erzeugt wurde).
     *
     * Entspricht PUT /letters/{id}/upload.
     */
    public function upload(int $id, string $base64Pdf): void
    {
        $payload = ['upload' => ['base64file' => $base64Pdf]];

        $this->putVoid("/letters/{$id}/upload", $payload);
    }

    /** Lädt eine unterschriebene PDF-Version des Briefs hoch. */
    public function uploadSignature(int $id, string $base64Pdf): void
    {
        $payload = ['upload' => ['base64file' => $base64Pdf]];

        $this->putVoid("/letters/{$id}/upload-signature", $payload);
    }

    /**
     * @return LetterPdf|string PDF-Model im JSON-Modus, binär im Raw-Modus
     */
    #[NoDiscard]
    public function pdf(int $id, ?InvoicePdfType $type = null, bool $rawPdf = false): LetterPdf|string
    {
        $query = [];

        if ($type instanceof InvoicePdfType) {
            $query['type'] = $type->value;
        }

        if ($rawPdf) {
            $query['format'] = 'pdf';

            return $this->getRaw("/letters/{$id}/pdf", $query);
        }

        $data = $this->getJson("/letters/{$id}/pdf", $query);

        return LetterPdf::fromArray($this->unwrapEnvelope($data, 'pdf', 'fetching letter PDF'));
    }
}
