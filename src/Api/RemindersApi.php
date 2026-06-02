<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Exception\AuthenticationException;
use Justpilot\Billomat\Exception\HttpException;
use Justpilot\Billomat\Exception\ValidationException;
use Justpilot\Billomat\Model\Enum\InvoicePdfType;
use Justpilot\Billomat\Model\Reminder;
use Justpilot\Billomat\Model\ReminderPdf;
use RuntimeException;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;

/**
 * API-Wrapper für die Billomat-Reminders-Ressource (Mahnungen).
 *
 * Doku: https://www.billomat.com/en/api/reminders/
 *
 * Endpoints:
 *  - GET    /reminders
 *  - GET    /reminders/{id}
 *  - POST   /reminders
 *  - PUT    /reminders/{id}
 *  - DELETE /reminders/{id}
 *  - PUT    /reminders/{id}/complete
 *  - PUT    /reminders/{id}/cancel
 *  - POST   /reminders/{id}/email
 *  - GET    /reminders/{id}/pdf
 *  - PUT    /reminders/{id}/upload-signature
 */
final class RemindersApi extends AbstractApi
{
    /**
     * @param array<string, scalar|array|null> $filters
     *
     * @return list<Reminder>
     */
    public function list(array $filters = []): array
    {
        $data = $this->getJson('/reminders', $filters);

        $node = $data['reminders']['reminder'] ?? [];

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

        /** @var list<Reminder> $models */
        $models = array_map(
            Reminder::fromArray(...),
            $rows,
        );

        return $models;
    }

    public function get(int $id): ?Reminder
    {
        $data = $this->getJsonOrNull("/reminders/{$id}");

        if (null === $data) {
            return null;
        }

        $row = $data['reminder'] ?? null;

        if (!\is_array($row)) {
            return null;
        }

        return Reminder::fromArray($row);
    }

    /**
     * @throws ValidationException
     * @throws AuthenticationException
     * @throws HttpException
     */
    public function create(ReminderCreateOptions $options): Reminder
    {
        $payload = ['reminder' => $options->toArray()];

        $data = $this->postJson('/reminders', $payload);

        $created = $data['reminder'] ?? null;

        if (!\is_array($created)) {
            throw new RuntimeException('Unexpected response from Billomat when creating reminder.');
        }

        return Reminder::fromArray($created);
    }

    public function update(int $id, ReminderUpdateOptions $options): Reminder
    {
        $payload = ['reminder' => $options->toArray()];

        $data = $this->putJson("/reminders/{$id}", $payload);

        $row = $data['reminder'] ?? null;
        if (!\is_array($row)) {
            throw new RuntimeException('Unexpected response from Billomat when updating reminder.');
        }

        return Reminder::fromArray($row);
    }

    public function complete(int $id, ?int $templateId = null): bool
    {
        $body = [];

        if (null !== $templateId) {
            $body['template_id'] = $templateId;
        }

        $payload = ['reminder' => $body];

        $response = $this->putEmptyResponse("/reminders/{$id}/complete", $payload);

        return 200 === $response->getStatusCode();
    }

    public function delete(int $id): bool
    {
        $this->deleteVoid("/reminders/{$id}");

        return true;
    }

    public function cancel(int $id): bool
    {
        $response = $this->putEmptyResponse("/reminders/{$id}/cancel");

        return 200 === $response->getStatusCode();
    }

    public function email(int $id, ?ReminderEmailOptions $options = null): bool
    {
        $payload = ['email' => $options?->toArray() ?? []];

        $this->postJson("/reminders/{$id}/email", $payload);

        return true;
    }

    public function uploadSignature(int $id, string $base64Pdf): bool
    {
        $payload = ['upload' => ['base64file' => $base64Pdf]];

        $response = $this->putEmptyResponse("/reminders/{$id}/upload-signature", $payload);

        return 200 === $response->getStatusCode();
    }

    /**
     * @return ReminderPdf|string PDF-Model im JSON-Modus, binär im Raw-Modus
     */
    public function pdf(int $id, ?InvoicePdfType $type = null, bool $rawPdf = false): ReminderPdf|string
    {
        $query = [];

        if ($type instanceof InvoicePdfType) {
            $query['type'] = $type->value;
        }

        if ($rawPdf) {
            $query['format'] = 'pdf';

            $response = $this->http->request('GET', "/reminders/{$id}/pdf", $query);

            try {
                return $response->getContent();
            } catch (HttpExceptionInterface $e) {
                throw $this->mapHttpException($e);
            }
        }

        $data = $this->getJson("/reminders/{$id}/pdf", $query);

        $pdfData = $data['pdf'] ?? null;

        if (!\is_array($pdfData)) {
            throw new RuntimeException('Unexpected response from Billomat when fetching reminder PDF.');
        }

        return ReminderPdf::fromArray($pdfData);
    }
}
