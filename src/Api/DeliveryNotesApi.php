<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Generator;
use Justpilot\Billomat\Exception\AuthenticationException;
use Justpilot\Billomat\Exception\HttpException;
use Justpilot\Billomat\Exception\ValidationException;
use Justpilot\Billomat\Model\DeliveryNote;
use Justpilot\Billomat\Model\DeliveryNotePdf;
use Justpilot\Billomat\Model\Enum\InvoicePdfType;
use Justpilot\Billomat\Pagination\Page;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;

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
 *  - PUT    /delivery-notes/{id}/cancel
 *  - PUT    /delivery-notes/{id}/clear
 *  - PUT    /delivery-notes/{id}/undo
 *  - POST   /delivery-notes/{id}/email
 *  - GET    /delivery-notes/{id}/pdf
 *  - PUT    /delivery-notes/{id}/upload-signature
 */
final class DeliveryNotesApi extends AbstractApi
{
    /**
     * @param array<string, scalar|array|null> $filters
     *
     * @return list<DeliveryNote>
     */
    public function list(array $filters = []): array
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
     * @param array<string, scalar|array|null> $filters
     *
     * @return Page<DeliveryNote>
     */
    public function listPage(array $filters = []): Page
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
     * @param array<string, scalar|array|null> $filters
     *
     * @return Generator<int, DeliveryNote>
     */
    public function iterateAll(array $filters = [], int $pageSize = 100): Generator
    {
        yield from $this->iterateResource('/delivery-notes', 'delivery-notes', 'delivery-note', DeliveryNote::fromArray(...), $filters, $pageSize);
    }

    public function get(int $id): ?DeliveryNote
    {
        $data = $this->getJsonOrNull("/delivery-notes/{$id}");

        if (null === $data) {
            return null;
        }

        $row = $data['delivery-note'] ?? null;

        if (!\is_array($row)) {
            return null;
        }

        return DeliveryNote::fromArray($row);
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

    public function complete(int $id, ?int $templateId = null): bool
    {
        $body = [];

        if (null !== $templateId) {
            $body['template_id'] = $templateId;
        }

        $payload = ['delivery-note' => $body];

        $this->putVoid("/delivery-notes/{$id}/complete", $payload);

        return true;
    }

    public function delete(int $id): bool
    {
        $this->deleteVoid("/delivery-notes/{$id}");

        return true;
    }

    public function cancel(int $id): bool
    {
        $this->putVoid("/delivery-notes/{$id}/cancel");

        return true;
    }

    public function clear(int $id): bool
    {
        $this->putVoid("/delivery-notes/{$id}/clear");

        return true;
    }

    public function undo(int $id): bool
    {
        $this->putVoid("/delivery-notes/{$id}/undo");

        return true;
    }

    public function email(int $id, ?DeliveryNoteEmailOptions $options = null): bool
    {
        $payload = ['email' => $options?->toArray() ?? []];

        $this->postJson("/delivery-notes/{$id}/email", $payload);

        return true;
    }

    public function uploadSignature(int $id, string $base64Pdf): bool
    {
        $payload = ['upload' => ['base64file' => $base64Pdf]];

        $this->putVoid("/delivery-notes/{$id}/upload-signature", $payload);

        return true;
    }

    /**
     * @return DeliveryNotePdf|string PDF-Model im JSON-Modus, binär im Raw-Modus
     */
    public function pdf(int $id, ?InvoicePdfType $type = null, bool $rawPdf = false): DeliveryNotePdf|string
    {
        $query = [];

        if ($type instanceof InvoicePdfType) {
            $query['type'] = $type->value;
        }

        if ($rawPdf) {
            $query['format'] = 'pdf';

            $response = $this->http->request('GET', "/delivery-notes/{$id}/pdf", $query);

            try {
                return $response->getContent();
            } catch (HttpExceptionInterface $e) {
                throw $this->mapHttpException($e);
            }
        }

        $data = $this->getJson("/delivery-notes/{$id}/pdf", $query);

        return DeliveryNotePdf::fromArray($this->unwrapEnvelope($data, 'pdf', 'fetching delivery note PDF'));
    }
}
