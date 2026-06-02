<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Generator;
use Justpilot\Billomat\Exception\AuthenticationException;
use Justpilot\Billomat\Exception\HttpException;
use Justpilot\Billomat\Exception\ValidationException;
use Justpilot\Billomat\Model\Enum\InvoicePdfType;
use Justpilot\Billomat\Model\Letter;
use Justpilot\Billomat\Model\LetterPdf;
use Justpilot\Billomat\Pagination\Page;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;

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
     * @param array<string, scalar|array|null> $filters
     *
     * @return list<Letter>
     */
    public function list(array $filters = []): array
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
     * @param array<string, scalar|array|null> $filters
     *
     * @return Page<Letter>
     */
    public function listPage(array $filters = []): Page
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
     * @param array<string, scalar|array|null> $filters
     *
     * @return Generator<int, Letter>
     */
    public function iterateAll(array $filters = [], int $pageSize = 100): Generator
    {
        yield from $this->iterateResource('/letters', 'letters', 'letter', Letter::fromArray(...), $filters, $pageSize);
    }

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

    public function complete(int $id, ?int $templateId = null): bool
    {
        $body = [];

        if (null !== $templateId) {
            $body['template_id'] = $templateId;
        }

        $payload = ['letter' => $body];

        $this->putVoid("/letters/{$id}/complete", $payload);

        return true;
    }

    public function delete(int $id): bool
    {
        $this->deleteVoid("/letters/{$id}");

        return true;
    }

    public function cancel(int $id): bool
    {
        $this->putVoid("/letters/{$id}/cancel");

        return true;
    }

    public function clear(int $id): bool
    {
        $this->putVoid("/letters/{$id}/clear");

        return true;
    }

    public function undo(int $id): bool
    {
        $this->putVoid("/letters/{$id}/undo");

        return true;
    }

    public function email(int $id, ?LetterEmailOptions $options = null): bool
    {
        $payload = ['email' => $options?->toArray() ?? []];

        $this->postJson("/letters/{$id}/email", $payload);

        return true;
    }

    /**
     * Lädt das komplette Brief-PDF hoch (z.B. wenn das Layout extern erzeugt wurde).
     *
     * Entspricht PUT /letters/{id}/upload.
     */
    public function upload(int $id, string $base64Pdf): bool
    {
        $payload = ['upload' => ['base64file' => $base64Pdf]];

        $this->putVoid("/letters/{$id}/upload", $payload);

        return true;
    }

    /** Lädt eine unterschriebene PDF-Version des Briefs hoch. */
    public function uploadSignature(int $id, string $base64Pdf): bool
    {
        $payload = ['upload' => ['base64file' => $base64Pdf]];

        $this->putVoid("/letters/{$id}/upload-signature", $payload);

        return true;
    }

    /**
     * @return LetterPdf|string PDF-Model im JSON-Modus, binär im Raw-Modus
     */
    public function pdf(int $id, ?InvoicePdfType $type = null, bool $rawPdf = false): LetterPdf|string
    {
        $query = [];

        if ($type instanceof InvoicePdfType) {
            $query['type'] = $type->value;
        }

        if ($rawPdf) {
            $query['format'] = 'pdf';

            $response = $this->http->request('GET', "/letters/{$id}/pdf", $query);

            try {
                return $response->getContent();
            } catch (HttpExceptionInterface $e) {
                throw $this->mapHttpException($e);
            }
        }

        $data = $this->getJson("/letters/{$id}/pdf", $query);

        return LetterPdf::fromArray($this->unwrapEnvelope($data, 'pdf', 'fetching letter PDF'));
    }
}
