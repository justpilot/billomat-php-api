<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Exception\AuthenticationException;
use Justpilot\Billomat\Exception\HttpException;
use Justpilot\Billomat\Exception\ValidationException;
use Justpilot\Billomat\Model\Confirmation;
use Justpilot\Billomat\Model\ConfirmationPdf;
use Justpilot\Billomat\Model\Enum\InvoicePdfType;
use RuntimeException;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;

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
     * @param array<string, scalar|array|null> $filters
     *
     * @return list<Confirmation>
     */
    public function list(array $filters = []): array
    {
        $data = $this->getJson('/confirmations', $filters);

        $node = $data['confirmations']['confirmation'] ?? [];

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

        /** @var list<Confirmation> $models */
        $models = array_map(
            Confirmation::fromArray(...),
            $rows,
        );

        return $models;
    }

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

        $created = $data['confirmation'] ?? null;

        if (!\is_array($created)) {
            throw new RuntimeException('Unexpected response from Billomat when creating confirmation.');
        }

        return Confirmation::fromArray($created);
    }

    public function update(int $id, ConfirmationUpdateOptions $options): Confirmation
    {
        $payload = ['confirmation' => $options->toArray()];

        $data = $this->putJson("/confirmations/{$id}", $payload);

        $row = $data['confirmation'] ?? null;
        if (!\is_array($row)) {
            throw new RuntimeException('Unexpected response from Billomat when updating confirmation.');
        }

        return Confirmation::fromArray($row);
    }

    public function complete(int $id, ?int $templateId = null): bool
    {
        $body = [];

        if (null !== $templateId) {
            $body['template_id'] = $templateId;
        }

        $payload = ['confirmation' => $body];

        $response = $this->putEmptyResponse("/confirmations/{$id}/complete", $payload);

        return 200 === $response->getStatusCode();
    }

    public function delete(int $id): bool
    {
        $this->deleteVoid("/confirmations/{$id}");

        return true;
    }

    public function cancel(int $id): bool
    {
        $response = $this->putEmptyResponse("/confirmations/{$id}/cancel");

        return 200 === $response->getStatusCode();
    }

    /** Markiert eine Auftragsbestätigung als erledigt (Status → CLEARED). */
    public function clear(int $id): bool
    {
        $response = $this->putEmptyResponse("/confirmations/{$id}/clear");

        return 200 === $response->getStatusCode();
    }

    /** Setzt einen Status zurück auf OPEN (Rückgängigmachen von clear/cancel). */
    public function undo(int $id): bool
    {
        $response = $this->putEmptyResponse("/confirmations/{$id}/undo");

        return 200 === $response->getStatusCode();
    }

    public function email(int $id, ?ConfirmationEmailOptions $options = null): bool
    {
        $payload = ['email' => $options?->toArray() ?? []];

        $this->postJson("/confirmations/{$id}/email", $payload);

        return true;
    }

    public function uploadSignature(int $id, string $base64Pdf): bool
    {
        $payload = ['upload' => ['base64file' => $base64Pdf]];

        $response = $this->putEmptyResponse("/confirmations/{$id}/upload-signature", $payload);

        return 200 === $response->getStatusCode();
    }

    /**
     * Ruft das PDF einer Auftragsbestätigung ab.
     *
     * @return ConfirmationPdf|string PDF-Model im JSON-Modus, binär im Raw-Modus
     */
    public function pdf(int $id, ?InvoicePdfType $type = null, bool $rawPdf = false): ConfirmationPdf|string
    {
        $query = [];

        if ($type instanceof InvoicePdfType) {
            $query['type'] = $type->value;
        }

        if ($rawPdf) {
            $query['format'] = 'pdf';

            $response = $this->http->request('GET', "/confirmations/{$id}/pdf", $query);

            try {
                return $response->getContent();
            } catch (HttpExceptionInterface $e) {
                throw $this->mapHttpException($e);
            }
        }

        $data = $this->getJson("/confirmations/{$id}/pdf", $query);

        $pdfData = $data['pdf'] ?? null;

        if (!\is_array($pdfData)) {
            throw new RuntimeException('Unexpected response from Billomat when fetching confirmation PDF.');
        }

        return ConfirmationPdf::fromArray($pdfData);
    }
}
