<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Exception\AuthenticationException;
use Justpilot\Billomat\Exception\HttpException;
use Justpilot\Billomat\Exception\ValidationException;
use Justpilot\Billomat\Model\Confirmation;
use Justpilot\Billomat\Model\ConfirmationPdf;
use Justpilot\Billomat\Model\Enum\InvoicePdfType;
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
        return $this->listResource('/confirmations', 'confirmations', 'confirmation', Confirmation::fromArray(...), $filters);
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

        return Confirmation::fromArray($this->unwrapEnvelope($data, 'confirmation', 'creating confirmation'));
    }

    public function update(int $id, ConfirmationUpdateOptions $options): Confirmation
    {
        $payload = ['confirmation' => $options->toArray()];

        $data = $this->putJson("/confirmations/{$id}", $payload);

        return Confirmation::fromArray($this->unwrapEnvelope($data, 'confirmation', 'updating confirmation'));
    }

    public function complete(int $id, ?int $templateId = null): bool
    {
        $body = [];

        if (null !== $templateId) {
            $body['template_id'] = $templateId;
        }

        $payload = ['confirmation' => $body];

        $this->putVoid("/confirmations/{$id}/complete", $payload);

        return true;
    }

    public function delete(int $id): bool
    {
        $this->deleteVoid("/confirmations/{$id}");

        return true;
    }

    public function cancel(int $id): bool
    {
        $this->putVoid("/confirmations/{$id}/cancel");

        return true;
    }

    /** Markiert eine Auftragsbestätigung als erledigt (Status → CLEARED). */
    public function clear(int $id): bool
    {
        $this->putVoid("/confirmations/{$id}/clear");

        return true;
    }

    /** Setzt einen Status zurück auf OPEN (Rückgängigmachen von clear/cancel). */
    public function undo(int $id): bool
    {
        $this->putVoid("/confirmations/{$id}/undo");

        return true;
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

        $this->putVoid("/confirmations/{$id}/upload-signature", $payload);

        return true;
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

        return ConfirmationPdf::fromArray($this->unwrapEnvelope($data, 'pdf', 'fetching confirmation PDF'));
    }
}
