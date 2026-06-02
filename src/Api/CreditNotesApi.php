<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Generator;
use Justpilot\Billomat\Exception\AuthenticationException;
use Justpilot\Billomat\Exception\HttpException;
use Justpilot\Billomat\Exception\ValidationException;
use Justpilot\Billomat\Model\CreditNote;
use Justpilot\Billomat\Model\CreditNotePdf;
use Justpilot\Billomat\Model\Enum\InvoicePdfType;
use Justpilot\Billomat\Pagination\Page;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;

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
     * @param array<string, scalar|array|null> $filters
     *
     * @return list<CreditNote>
     */
    public function list(array $filters = []): array
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
     * @param array<string, scalar|array|null> $filters
     *
     * @return Page<CreditNote>
     */
    public function listPage(array $filters = []): Page
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
     * @param array<string, scalar|array|null> $filters
     *
     * @return Generator<int, CreditNote>
     */
    public function iterateAll(array $filters = [], int $pageSize = 100): Generator
    {
        yield from $this->iterateResource('/credit-notes', 'credit-notes', 'credit-note', CreditNote::fromArray(...), $filters, $pageSize);
    }

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

    public function complete(int $id, ?int $templateId = null): bool
    {
        $body = [];

        if (null !== $templateId) {
            $body['template_id'] = $templateId;
        }

        $payload = ['credit-note' => $body];

        $this->putVoid("/credit-notes/{$id}/complete", $payload);

        return true;
    }

    public function delete(int $id): bool
    {
        $this->deleteVoid("/credit-notes/{$id}");

        return true;
    }

    public function cancel(int $id): bool
    {
        $this->putVoid("/credit-notes/{$id}/cancel");

        return true;
    }

    public function uncancel(int $id): bool
    {
        $this->putVoid("/credit-notes/{$id}/uncancel");

        return true;
    }

    public function email(int $id, ?CreditNoteEmailOptions $options = null): bool
    {
        $payload = ['email' => $options?->toArray() ?? []];

        $this->postJson("/credit-notes/{$id}/email", $payload);

        return true;
    }

    public function uploadSignature(int $id, string $base64Pdf): bool
    {
        $payload = ['upload' => ['base64file' => $base64Pdf]];

        $this->putVoid("/credit-notes/{$id}/upload-signature", $payload);

        return true;
    }

    /**
     * @return CreditNotePdf|string PDF-Model im JSON-Modus, binär im Raw-Modus
     */
    public function pdf(int $id, ?InvoicePdfType $type = null, bool $rawPdf = false): CreditNotePdf|string
    {
        $query = [];

        if ($type instanceof InvoicePdfType) {
            $query['type'] = $type->value;
        }

        if ($rawPdf) {
            $query['format'] = 'pdf';

            $response = $this->http->request('GET', "/credit-notes/{$id}/pdf", $query);

            try {
                return $response->getContent();
            } catch (HttpExceptionInterface $e) {
                throw $this->mapHttpException($e);
            }
        }

        $data = $this->getJson("/credit-notes/{$id}/pdf", $query);

        return CreditNotePdf::fromArray($this->unwrapEnvelope($data, 'pdf', 'fetching credit note PDF'));
    }
}
