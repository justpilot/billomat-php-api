<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Generator;
use Justpilot\Billomat\Exception\AuthenticationException;
use Justpilot\Billomat\Exception\HttpException;
use Justpilot\Billomat\Exception\NotFoundException;
use Justpilot\Billomat\Exception\UnexpectedResponseException;
use Justpilot\Billomat\Exception\ValidationException;
use Justpilot\Billomat\Model\Enum\LetterCommentActionKey;
use Justpilot\Billomat\Model\Enum\PdfType;
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
     * @param LetterFilter|array<string, scalar|array<scalar|null>|null> $filters
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
     * @param LetterFilter|array<string, scalar|array<scalar|null>|null> $filters
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
     * @param LetterFilter|array<string, scalar|array<scalar|null>|null> $filters
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
        return $this->getResource("/letters/{$id}", 'letter', Letter::fromArray(...), 'fetching letter');
    }

    /**
     * Wie {@see get()}, wirft aber eine {@see NotFoundException} statt null zurückzugeben.
     *
     * @throws NotFoundException wenn es den Datensatz nicht gibt (404)
     */
    #[NoDiscard]
    public function getOrFail(int $id): Letter
    {
        return $this->get($id) ?? throw $this->notFound("/letters/{$id}");
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

    public function email(int $id, ?EmailOptions $options = null): void
    {
        $payload = ['email' => $options?->toArray() ?? []];

        $this->postJson("/letters/{$id}/email", $payload);
    }

    /**
     * Versendet einen Brief postalisch über den Pixelletter-Service.
     *
     * Entspricht POST /letters/{id}/mail.
     *
     * ⚠️ Kostenpflichtige Aktion.
     */
    public function mail(int $id, ?MailOptions $options = null): void
    {
        $payload = ['mail' => $options?->toArray() ?? []];

        $this->postJson("/letters/{$id}/mail", $payload);
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
     * Ruft das PDF eines Briefs als JSON ab (Metadaten und base64-kodierter Inhalt).
     *
     * Entspricht GET /letters/{id}/pdf. Optionaler Typ: `PdfType::SIGNED` (type=signed)
     * oder `PdfType::PRINT` (type=print). Die rohen Bytes liefert {@see pdfContent()}.
     */
    #[NoDiscard]
    public function pdf(int $id, ?PdfType $type = null): LetterPdf
    {
        $data = $this->getJson("/letters/{$id}/pdf", self::pdfQuery($type));

        return LetterPdf::fromArray($this->unwrapEnvelope($data, 'pdf', 'fetching letter PDF'));
    }

    /**
     * Ruft das PDF eines Briefs als binären Inhalt ab (GET /letters/{id}/pdf?format=pdf).
     *
     * @throws UnexpectedResponseException wenn Billomat kein PDF liefert
     */
    #[NoDiscard]
    public function pdfContent(int $id, ?PdfType $type = null): string
    {
        return $this->getPdf("/letters/{$id}/pdf", [...self::pdfQuery($type), 'format' => 'pdf']);
    }
}
