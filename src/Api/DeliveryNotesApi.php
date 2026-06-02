<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Exception\AuthenticationException;
use Justpilot\Billomat\Exception\HttpException;
use Justpilot\Billomat\Exception\ValidationException;
use Justpilot\Billomat\Model\DeliveryNote;
use Justpilot\Billomat\Model\DeliveryNotePdf;
use Justpilot\Billomat\Model\Enum\InvoicePdfType;
use RuntimeException;
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
        $data = $this->getJson('/delivery-notes', $filters);

        $node = $data['delivery-notes']['delivery-note'] ?? [];

        if (null === $node || [] === $node) {
            return [];
        }

        if (\is_array($node) && array_is_list($node)) {
            $rows = $node;
        } elseif (\is_array($node)) {
            $rows = [$node];
        } else {
            $rows = [];
        }

        /** @var list<DeliveryNote> $models */
        $models = array_map(
            DeliveryNote::fromArray(...),
            $rows,
        );

        return $models;
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

        $created = $data['delivery-note'] ?? null;

        if (!\is_array($created)) {
            throw new RuntimeException('Unexpected response from Billomat when creating delivery note.');
        }

        return DeliveryNote::fromArray($created);
    }

    public function update(int $id, DeliveryNoteUpdateOptions $options): DeliveryNote
    {
        $payload = ['delivery-note' => $options->toArray()];

        $data = $this->putJson("/delivery-notes/{$id}", $payload);

        $row = $data['delivery-note'] ?? null;
        if (!\is_array($row)) {
            throw new RuntimeException('Unexpected response from Billomat when updating delivery note.');
        }

        return DeliveryNote::fromArray($row);
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

        $pdfData = $data['pdf'] ?? null;

        if (!\is_array($pdfData)) {
            throw new RuntimeException('Unexpected response from Billomat when fetching delivery note PDF.');
        }

        return DeliveryNotePdf::fromArray($pdfData);
    }
}
